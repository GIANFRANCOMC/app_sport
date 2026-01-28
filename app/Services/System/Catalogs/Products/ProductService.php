<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Products;

use Exception;
use App\Helpers\System\{TranslationHelper, Utilities};
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

use App\Models\System\Catalogs\{CategoryItem, Item};
use App\Models\System\Organizations\Branch;
use App\Models\System\Warehouses\WarehouseItem;

/**
 * Service class for managing module operations
 * Handles business logic for creating and updating records
 */
class ProductService {

    /**
     * Translation namespace for module
     */
    private const TRANSLATION_NAMESPACE = "System.Catalogs.product";

    /**
     * Allowed fields for record creation and update
     */
    private const ALLOWED_FIELDS = [
        "internal_code",
        "name",
        "description",
        "price",
        "min_price",
        "max_price",
        "currency_id",
        "see_my_web",
        "see_my_web_price",
        "status"
    ];

    /**
     * Searchable fields for filtering
     */
    private const SEARCHABLE_FIELDS = [
        "internal_code",
        "name",
        "description",
        "price"
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
     * Prepare data for creation
     *
     * @param array $data Input data
     * @param int $companyId Company
     * @param int $userId User
     * @return array
     */
    private static function prepareProductDataForCreate(array $data, int $companyId, int $userId): array {

        $itemData = [
            "company_id"     => $companyId,
            "type"           => "product",
            "status"         => $data["status"] ?? "active",
            "created_at"     => now(),
            "created_by"     => $userId
        ];

        foreach(self::ALLOWED_FIELDS as $field) {

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
     * Prepare data for update (only changed fields)
     *
     * @param Item $item Record instance
     * @param array $data Input data
     * @return array
     */
    private static function prepareProductDataForUpdate(Item $item, array $data): array {

        $updateData = [];

        foreach(self::ALLOWED_FIELDS as $field) {

            if(isset($data[$field])) {

                if(in_array($field, ["min_price", "max_price"])) {

                    $value = floatval($data[$field]) <= 0 ? null : $data[$field];
                    if($value !== $item->$field) {
                        $updateData[$field] = $value;
                    }

                }elseif($field === "see_my_web_price") {

                    $value = ($data["see_my_web"] ?? $item->see_my_web) ? ($data[$field] ?? false) : false;
                    if($value !== $item->$field) {
                        $updateData[$field] = $value;
                    }

                }elseif($data[$field] !== $item->$field) {

                    $updateData[$field] = $data[$field];

                }

            }

        }

        return $updateData;

    }

    /**
     * Create warehouse items for a product
     *
     * @param Item $item Item instance
     * @param int $companyId Company
     * @param int $userId User
     * @return void
     */
    private static function createWarehouseItems(Item $item, int $companyId, int $userId): void {

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
     * @param int $userId User
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
     * Check if internal code exists
     *
     * @param string $internalCode Internal code
     * @param int $companyId Company
     * @param int|null $excludeId Item ID to exclude
     * @return bool
     */
    private static function internalCodeExists(string $internalCode, int $companyId, ?int $excludeId = null): bool {

        $query = Item::where("internal_code", $internalCode)
                     ->where("company_id", $companyId)
                     ->where("type", "product");

        if(Utilities::isDefined($excludeId)) {

            $query->where("id", "!=", $excludeId);

        }

        return $query->exists();

    }

    /**
     * Create a new record
     *
     * @param array $data Input data
     * @param int|null $userId User creating the record
     * @return Item|null Created record instance or null on failure
     * @throws Exception
     */
    public static function create(array $data, ?int $userId = null): ?Item {

        $item = null;

        DB::transaction(function() use($data, $userId, &$item) {

            $userAuth  = Auth::user();
            $companyId = $data["company_id"] ?? $userAuth->company_id ?? null;

            if(!$companyId) {

                throw new Exception(self::trans("company_id_required"));

            }

            $userId = $userId ?? $userAuth->id ?? null;

            // Check if internal code exists
            if(self::internalCodeExists($data["internal_code"], $companyId)) {

                throw new Exception(self::trans("internal_code_exists"));

            }

            // Prepare data with only allowed fields
            $itemData = self::prepareProductDataForCreate($data, $companyId, $userId);

            // Create the record
            $item = Item::create($itemData);

            // Create warehouse items for products
            self::createWarehouseItems($item, $companyId, $userId);

            // Sync categories
            if(isset($data["categories"]) && is_array($data["categories"])) {

                self::syncCategories($item, $data["categories"], $userId);

            }

        });

        return $item;

    }

    /**
     * Update an existing record
     *
     * @param Item $item Record instance to update
     * @param array $data Input data
     * @param int|null $userId User updating the record
     * @return Item Updated record instance
     */
    public static function update(Item $item, array $data, ?int $userId = null): Item {

        DB::transaction(function() use($item, $data, $userId) {

            $userAuth = Auth::user();
            $userId   = $userId ?? $userAuth->id ?? null;

            // Check if internal code exists (excluding current item)
            if(isset($data["internal_code"])) {

                if(self::internalCodeExists($data["internal_code"], $item->company_id, $item->id)) {

                    throw new Exception(self::trans("internal_code_exists"));

                }

            }

            // Prepare update data with only changed fields
            $updateData = self::prepareProductDataForUpdate($item, $data);

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
     * Find record by ID and company ID
     *
     * @param int $id Record
     * @param int $companyId Company
     * @param bool $activeOnly Only search active records
     * @param array $relations Relations to eager load
     * @return Item|null
     */
    public static function findByIdAndCompany(int $id, int $companyId, bool $activeOnly = false, array $relations = ["currency", "categoryItems"]): ?Item {

        $query = Item::where("id", $id)
                     ->where("company_id", $companyId)
                     ->where("type", "product");

        if($activeOnly) {

            $query->where("status", "active");

        }

        if(!empty($relations)) {

            $query->with($relations);

        }

        return $query->first();

    }

    /**
     * Get paginated list of records with filters
     *
     * @param int $companyId Company
     * @param array $filters Filter parameters (filter_by, word)
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator {

        $query = Item::where("company_id", $companyId)
                     ->where("type", "product")
                     ->with(["currency", "categoryItems"]);

        // Apply filters
        $filterBy = $filters["filter_by"] ?? null;
        $word     = $filters["word"] ?? null;

        if(Utilities::isDefined($word) && Utilities::isDefined($filterBy)) {

            $searchTerm = "%{$word}%";

            if($filterBy === "all") {

                // Search across all searchable fields
                $query->where(function(Builder $q) use($searchTerm) {

                    $searchableFields = self::SEARCHABLE_FIELDS;
                    $firstField       = array_shift($searchableFields);

                    if($firstField) {

                        $q->where($firstField, "like", $searchTerm);

                    }

                    foreach($searchableFields as $field) {

                        $q->orWhere($field, "like", $searchTerm);

                    }

                });

            }elseif(in_array($filterBy, self::SEARCHABLE_FIELDS, true)) {

                // Search in specific field
                $query->where($filterBy, "like", $searchTerm);

            }

        }

        return $query->orderBy("name", "ASC")
                     ->paginate($perPage);

    }

}
