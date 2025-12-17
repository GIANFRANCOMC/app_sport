<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Items;

use Exception;
use App\Helpers\System\{TranslationHelper, Utilities};
use Illuminate\Support\Facades\{Auth, DB};

use App\Models\System\Catalogs\{CategoryItem, Item};
use App\Models\System\Organizations\Branch;
use App\Models\System\Warehouses\WarehouseItem;

/**
 * Base service class for managing Item operations
 * Handles business logic for creating and updating items (products, services, subscriptions)
 */
class ItemService {

    /**
     * Translation namespace for item module
     */
    private const TRANSLATION_NAMESPACE = "System.Catalogs.item";

    /**
     * Allowed fields for item creation and update
     */
    private const ALLOWED_FIELDS = [
        "internal_code",
        "name",
        "description",
        "price",
        "min_price",
        "max_price",
        "currency_id",
        "type",
        "duration_type",
        "duration_value",
        "see_my_web",
        "see_my_web_price",
        "status"
    ];

    /**
     * Get translation with fallback
     *
     * @param string $key Translation key
     * @param array $replace Replacements
     * @return string
     */
    private static function trans(string $key, array $replace = []): string {

        return TranslationHelper::getWithFallback(self::TRANSLATION_NAMESPACE, $key, $replace);

    }

    /**
     * Prepare item data for creation
     *
     * @param array $data Input data
     * @param int $companyId Company ID
     * @param int $userId User ID
     * @param string $type Item type
     * @return array
     */
    private static function prepareItemDataForCreate(array $data, int $companyId, int $userId, string $type): array {

        $itemData = [
            "company_id"     => $companyId,
            "type"           => $type,
            "status"         => $data["status"] ?? "active",
            "created_at"     => now(),
            "created_by"     => $userId
        ];

        foreach(self::ALLOWED_FIELDS as $field) {

            if($field === "type") continue; // Already set

            if(isset($data[$field])) {

                if(in_array($field, ["min_price", "max_price"])) {

                    $itemData[$field] = floatval($data[$field]) <= 0 ? null : $data[$field];

                }elseif($field === "see_my_web_price") {

                    $itemData[$field] = ($data["see_my_web"] ?? false) ? ($data[$field] ?? false) : false;

                }else {

                    $itemData[$field] = $data[$field];

                }

            }

        }

        return $itemData;

    }

    /**
     * Create warehouse items for a product
     *
     * @param Item $item Item instance
     * @param int $companyId Company ID
     * @param int $userId User ID
     * @return void
     */
    private static function createWarehouseItems(Item $item, int $companyId, int $userId): void {

        if($item->type !== "product") return;

        $branches = Branch::getAll("default", $companyId);

        foreach($branches as $branch) {

            foreach($branch->warehouses as $warehouse) {

                $warehouseItem = new WarehouseItem();
                $warehouseItem->warehouse_id = $warehouse->id;
                $warehouseItem->item_id      = $item->id;
                $warehouseItem->quantity     = 0;
                $warehouseItem->status       = $item->status;
                $warehouseItem->created_at   = now();
                $warehouseItem->created_by   = $userId;
                $warehouseItem->save();

            }

        }

    }

    /**
     * Sync categories for an item
     *
     * @param Item $item Item instance
     * @param array $categories Categories data
     * @param int $userId User ID
     * @return void
     */
    private static function syncCategories(Item $item, array $categories, int $userId): void {

        // Deactivate existing categories
        CategoryItem::where("item_id", $item->id)
                    ->where("status", "active")
                    ->update([
                        "status"     => "inactive",
                        "updated_at" => now(),
                        "updated_by" => $userId
                    ]);

        // Create/update new categories
        foreach($categories as $category) {

            CategoryItem::updateOrInsert(
                [
                    "category_id" => $category["category_id"],
                    "item_id"     => $item->id
                ],
                [
                    "status"      => "active",
                    "updated_at"  => now(),
                    "updated_by"  => $userId
                ]
            );

        }

    }

    /**
     * Create a new item
     *
     * @param array $data Item data from request
     * @param string $type Item type (product, service, subscription)
     * @param int|null $userId User ID creating the item
     * @return Item|null Created item instance or null on failure
     * @throws \Exception
     */
    public static function create(array $data, string $type, ?int $userId = null): ?Item {

        $item = null;

        DB::transaction(function() use($data, $type, $userId, &$item) {

            $userAuth  = Auth::user();
            $companyId = $data["company_id"] ?? $userAuth->company_id ?? null;

            if(!$companyId) {

                throw new Exception(self::trans("company_id_required"));

            }

            $userId = $userId ?? $userAuth->id;

            // Check if internal code exists
            $itemExists = Item::where("company_id", $companyId)
                              ->where("internal_code", $data["internal_code"])
                              ->exists();

            if($itemExists) {

                throw new Exception(self::trans("internal_code_exists"));

            }

            // Prepare item data
            $itemData = self::prepareItemDataForCreate($data, $companyId, $userId, $type);

            // Create the item
            $item = Item::create($itemData);

            // Create warehouse items for products
            if($type === "product") {

                self::createWarehouseItems($item, $companyId, $userId);

            }

            // Sync categories
            if(isset($data["categories"]) && is_array($data["categories"])) {

                self::syncCategories($item, $data["categories"], $userId);

            }

        });

        return $item;

    }

    /**
     * Update an existing item
     *
     * @param Item $item Item instance to update
     * @param array $data Updated item data
     * @param int|null $userId User ID updating the item
     * @return Item Updated item instance
     * @throws \Exception
     */
    public static function update(Item $item, array $data, ?int $userId = null): Item {

        DB::transaction(function() use($item, $data, $userId) {

            $userAuth = Auth::user();
            $userId   = $userId ?? $userAuth->id;

            // Check if internal code exists (excluding current item)
            if(isset($data["internal_code"])) {

                $itemExists = Item::where("company_id", $item->company_id)
                                  ->where("internal_code", $data["internal_code"])
                                  ->where("id", "!=", $item->id)
                                  ->exists();

                if($itemExists) {

                    throw new Exception(self::trans("internal_code_exists"));

                }

            }

            // Prepare update data
            $updateData = [];

            foreach(self::ALLOWED_FIELDS as $field) {

                if(isset($data[$field])) {

                    if(in_array($field, ["min_price", "max_price"])) {

                        $updateData[$field] = floatval($data[$field]) <= 0 ? null : $data[$field];

                    }elseif($field === "see_my_web_price") {

                        $updateData[$field] = ($data["see_my_web"] ?? $item->see_my_web) ? ($data[$field] ?? false) : false;

                    }else {

                        $updateData[$field] = $data[$field];

                    }

                }

            }

            // Only update if there are changes
            if(!empty($updateData)) {

                $updateData["updated_at"] = now();
                $updateData["updated_by"] = $userId;
                $item->update($updateData);

            }

            // Sync categories
            if(isset($data["categories"]) && is_array($data["categories"])) {

                self::syncCategories($item, $data["categories"], $userId);

            }

        });

        return $item->fresh(["currency", "categoryItems"]);

    }

    /**
     * Find item by ID and company ID
     *
     * @param int $id Item ID
     * @param int $companyId Company ID
     * @param string $type Item type
     * @return Item|null
     */
    public static function findByIdAndCompany(int $id, int $companyId, string $type): ?Item {

        return Item::where("id", $id)
                   ->where("company_id", $companyId)
                   ->where("type", $type)
                   ->first();

    }

    /**
     * Get paginated list of items
     *
     * @param int $companyId Company ID
     * @param string $type Item type
     * @param array $filters Filter parameters
     * @param int $perPage Items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, string $type, array $filters = [], int $perPage = 15) {

        $query = Item::where("company_id", $companyId)
                     ->where("type", $type)
                     ->with(["currency", "categoryItems"]);

        // Apply search filters
        self::applySearchFilters($query, $filters);

        // Apply ordering
        $query->orderBy("name", "ASC");

        return $query->paginate($perPage);

    }

    /**
     * Apply search filters to query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return void
     */
    private static function applySearchFilters($query, array $filters): void {

        $filterBy = $filters["filter_by"] ?? null;
        $word     = $filters["word"] ?? null;

        if(!Utilities::isDefined($filterBy) || !Utilities::isDefined($word)) {

            return;

        }

        $searchTerm = Utilities::getWordSearch($word);
        $searchableFields = ["internal_code", "name", "description", "price"];

        if($filterBy === "all") {

            // Search across all searchable fields
            $query->where(function($q) use($searchTerm, $searchableFields) {

                foreach($searchableFields as $field) {

                    $q->orWhere($field, "like", $searchTerm);

                }

            });

        }elseif(in_array($filterBy, $searchableFields, true)) {

            // Search in specific field
            $query->where($filterBy, "like", $searchTerm);

        }

    }

}

