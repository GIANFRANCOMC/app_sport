<?php

declare(strict_types=1);

namespace App\Services\System\Warehouses;

use App\Helpers\System\Utilities;
use App\Models\System\Catalogs\Item;
use App\Models\System\Warehouses\{Warehouse, WarehouseItem};
use Illuminate\Support\Facades\DB;

/**
 * Service class for managing Stock Management operations
 * Handles business logic for managing warehouse stock
 */
class StockManagementService {

    /**
     * Get paginated list of items with stock information
     *
     * @param int $companyId Company ID
     * @param int $warehouseId Warehouse ID
     * @param int $perPage Items per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getPaginatedList(int $companyId, int $warehouseId, int $perPage) {

        return Item::where("company_id", $companyId)
                   ->whereIn("type", ["product"])
                   ->withSum(["warehouseItems as stock_quantity" => function($query) use($warehouseId) {

                       $query->where("warehouse_id", $warehouseId);

                   }], "quantity")
                   ->orderBy("name", "ASC")
                   ->paginate($perPage);

    }

    /**
     * Validate warehouse belongs to company
     *
     * @param int $warehouseId Warehouse ID
     * @param int $companyId Company ID
     * @return Warehouse|null
     */
    public static function validateWarehouse(int $warehouseId, int $companyId): ?Warehouse {

        return Warehouse::where("id", $warehouseId)
                        ->whereHas("branch", function($query) use($companyId) {

                            $query->where("company_id", $companyId);

                        })
                        ->first();

    }

    /**
     * Update stock for items in a warehouse
     *
     * @param int $warehouseId Warehouse ID
     * @param array $items Array of items with stock quantities
     * @param int|null $userId User ID performing the action
     * @return bool
     */
    public static function updateStock(int $warehouseId, array $items, ?int $userId = null): bool {

        DB::transaction(function() use($warehouseId, $items, $userId) {

            foreach($items as $item) {

                $warehouseItem = WarehouseItem::where("warehouse_id", $warehouseId)
                                              ->where("item_id", $item["id"])
                                              ->first();

                if($warehouseItem) {

                    $warehouseItem->update([
                        "quantity"   => floatval($item["stock_quantity"]),
                        "status"     => "active",
                        "updated_at" => now(),
                        "updated_by" => $userId
                    ]);

                }else {

                    WarehouseItem::create([
                        "warehouse_id" => $warehouseId,
                        "item_id"      => $item["id"],
                        "quantity"     => floatval($item["stock_quantity"]),
                        "status"       => "active",
                        "created_at"   => now(),
                        "created_by"   => $userId
                    ]);

                }

            }

        });

        return true;

    }

}

