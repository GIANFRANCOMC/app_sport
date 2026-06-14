<?php

declare(strict_types=1);

namespace App\Services\System\Warehouses\StockManagement;

use App\Helpers\System\Utilities;
use App\Models\System\Catalogs\Item;
use App\Models\System\Warehouses\{Warehouse, WarehouseItem};
use App\Services\System\Warehouses\Inventory\InventoryMovementService;
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
                   ->withSum(["warehouseItems as minimum_stock" => function($query) use($warehouseId) {

                       $query->where("warehouse_id", $warehouseId);

                   }], "minimum_stock")
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

            $warehouse = Warehouse::with("branch:id,company_id")
                ->findOrFail($warehouseId);

            foreach($items as $item) {

                $warehouseItem = WarehouseItem::where("warehouse_id", $warehouseId)
                                              ->where("item_id", $item["id"])
                                              ->first();

                $currentQuantity = round((float) ($warehouseItem?->quantity ?? 0), 2);
                $resultingBalance = round((float) ($item["stock_quantity"] ?? 0), 2);

                if(abs($currentQuantity - $resultingBalance) < 0.00001) {

                    continue;

                }

                InventoryMovementService::apply([
                    "company_id"       => (int) $warehouse->branch->company_id,
                    "warehouse_id"     => $warehouseId,
                    "item_id"          => (int) $item["id"],
                    "user_id"          => $userId,
                    "movement_type"    => InventoryMovementService::TYPE_CORRECTION,
                    "origin_type"      => InventoryMovementService::ORIGIN_MANUAL,
                    "resulting_balance" => $resultingBalance,
                    "reason"           => "Corrección manual desde Inventario."
                ]);

            }

        });

        return true;

    }

    public static function createManualMovement(
        int $companyId,
        int $warehouseId,
        int $itemId,
        string $movementType,
        ?float $quantity,
        ?float $resultingBalance,
        string $reason,
        ?int $userId = null
    ) {

        return InventoryMovementService::apply([
            "company_id"       => $companyId,
            "warehouse_id"     => $warehouseId,
            "item_id"          => $itemId,
            "user_id"          => $userId,
            "movement_type"    => $movementType,
            "origin_type"      => InventoryMovementService::ORIGIN_MANUAL,
            "quantity"         => $quantity,
            "resulting_balance" => $resultingBalance,
            "reason"           => $reason
        ]);

    }

    public static function getKardex(int $companyId, array $filters, int $perPage) {

        return InventoryMovementService::getPaginatedKardex($companyId, $filters, $perPage);

    }

    public static function transfer(array $data) {

        return InventoryMovementService::transfer($data);

    }

}
