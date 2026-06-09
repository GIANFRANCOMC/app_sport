<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Products;

use Exception;
use App\Helpers\System\{TranslationHelper, Utilities};
use Illuminate\Support\Facades\{Auth, DB};
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

use App\Services\System\Catalogs\Categories\{CategoryItemService};
use App\Services\System\Warehouses\Warehouses\{WarehouseItemService};
use App\Models\System\Catalogs\{Brand, Item};

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
        "barcode",
        "brand_id",
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
        "barcode",
        "name",
        "description",
        "price"
    ];

    private static function hasInputField(array $data, string $field): bool {

        return array_key_exists($field, $data);

    }

    private static function normalizeOptionalPrice(mixed $value): ?float {

        if($value === null || $value === "" || (is_numeric($value) && (float) $value <= 0)) {

            return null;

        }

        return (float) $value;

    }

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
            "company_id" => $companyId,
            "type"       => "product",
            "status"     => $data["status"] ?? "active",
            "created_at" => now(),
            "created_by" => $userId
        ];

        foreach(self::ALLOWED_FIELDS as $field) {

            if(self::hasInputField($data, $field)) {

                if(in_array($field, ["min_price", "max_price"])) {

                    $itemData[$field] = self::normalizeOptionalPrice($data[$field]);

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

            if(self::hasInputField($data, $field)) {

                if(in_array($field, ["min_price", "max_price"])) {

                    $value = self::normalizeOptionalPrice($data[$field]);

                    if($value !== ($item->$field === null ? null : (float) $item->$field)) {

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

            self::assertBrandCanBeAssigned($data["brand_id"] ?? null, $companyId);

            // Prepare data with only allowed fields
            $itemData = self::prepareProductDataForCreate($data, $companyId, $userId);

            // Create the record
            $item = Item::create($itemData);

            // Create warehouse items for products
            WarehouseItemService::syncProductInventory(
                $item->id,
                $companyId,
                $data["inventory"] ?? [],
                $userId,
                true
            );

            // Sync categories
            if(isset($data["categories"]) && is_array($data["categories"])) {

                CategoryItemService::sync($item->id, $data["categories"], $userId);

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

            self::assertBrandCanBeAssigned(
                $data["brand_id"] ?? null,
                (int) $item->company_id,
                $item
            );

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

                CategoryItemService::sync($item->id, $data["categories"], $userId);

            }

            WarehouseItemService::syncProductInventory(
                $item->id,
                (int) $item->company_id,
                $data["inventory"] ?? [],
                $userId,
                false
            );

        });

        return $item->fresh(["brand", "currency", "categoryItems", "warehouseItems.warehouse.branch"]);

    }

    /**
     * Find record by ID and company ID
     *
     * @param int $id Record
     * @param int $companyId Company
     * @param array|null $statuses Filter by statuses (e.g. ["active"], ["active", "inactive"])
     * @param array $relations Relations to eager load
     * @return Item|null
     */
    public static function findByIdAndCompany(int $id, int $companyId, ?array $statuses = ["active"], array $relations = ["brand", "currency", "categoryItems", "warehouseItems.warehouse.branch"]): ?Item {

        $query = Item::where("id", $id)
                     ->where("company_id", $companyId)
                     ->where("type", "product");

        if($statuses !== null && !empty($statuses)) {

            $query->whereIn("status", $statuses);

        }

        if($relations !== null && !empty($relations)) {

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
                     ->with(["brand", "currency", "categoryItems", "warehouseItems.warehouse.branch"]);

        // Apply filters
        $filterBy = $filters["filter_by"] ?? null;
        $word     = $filters["word"] ?? null;

        if(Utilities::isDefined($word) && Utilities::isDefined($filterBy)) {

            $searchTerm = Utilities::getWordSearch($word);

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

                    $q->orWhereHas("brand", function(Builder $brandQuery) use($searchTerm) {

                        $brandQuery->where("name", "like", $searchTerm);

                    });

                });

            }elseif($filterBy === "brand") {

                $query->whereHas("brand", function(Builder $brandQuery) use($searchTerm) {

                    $brandQuery->where("name", "like", $searchTerm);

                });

            }elseif(in_array($filterBy, self::SEARCHABLE_FIELDS, true)) {

                // Search in specific field
                $query->where($filterBy, "like", $searchTerm);

            }

        }

        return $query->orderBy("name", "ASC")
                     ->paginate($perPage);

    }

    private static function assertBrandCanBeAssigned(?int $brandId, int $companyId, ?Item $currentItem = null): void {

        if(!$brandId) {

            return;

        }

        $brand = Brand::query()
                      ->whereKey($brandId)
                      ->where("company_id", $companyId)
                      ->first();

        $keepsCurrentInactiveBrand = $brand
            && $brand->status === "inactive"
            && (int) ($currentItem?->brand_id ?? 0) === (int) $brand->id;

        if(!$brand || ($brand->status !== "active" && !$keepsCurrentInactiveBrand)) {

            throw new \InvalidArgumentException("La marca seleccionada no está disponible para la empresa.");

        }

    }

}
