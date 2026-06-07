<?php

declare(strict_types=1);

namespace App\Services\System\Warehouses\Warehouses;

use App\Models\System\Catalogs\{Item};
use App\Models\System\Warehouses\{Warehouse, WarehouseItem};

class WarehouseItemService {

    public static function syncProductInventory(
        int $itemId,
        int $companyId,
        array $inventory,
        ?int $userId = null,
        bool $setInitialStock = false
    ): void {

        $inventoryByWarehouse = collect($inventory)->keyBy(
            fn(array $record) => (int) ($record["warehouse_id"] ?? 0)
        );

        $warehouses = Warehouse::where("status", "active")
            ->whereHas("branch", function($query) use($companyId) {

                $query->where("company_id", $companyId)
                      ->where("status", "active");

            })
            ->get();

        foreach($warehouses as $warehouse) {

            $inventoryRecord = $inventoryByWarehouse->get((int) $warehouse->id, []);
            $warehouseItem = WarehouseItem::firstOrNew([
                "warehouse_id" => $warehouse->id,
                "item_id"      => $itemId
            ]);
            $isNew = !$warehouseItem->exists;

            if($isNew) {

                $warehouseItem->quantity   = $setInitialStock
                    ? (float) ($inventoryRecord["initial_stock"] ?? 0)
                    : 0;
                $warehouseItem->created_at = now();
                $warehouseItem->created_by = $userId;

            }

            $warehouseItem->minimum_stock = (float) (
                $inventoryRecord["minimum_stock"] ?? $warehouseItem->minimum_stock ?? 0
            );
            $warehouseItem->status = "active";

            if(!$isNew) {

                $warehouseItem->updated_at = now();
                $warehouseItem->updated_by = $userId;

            }

            $warehouseItem->save();

        }

    }

    public static function createForWarehouse(int $warehouseId, int $companyId, ?int $userId = null): void {

        $productIds = Item::where("company_id", $companyId)
            ->where("type", "product")
            ->pluck("id");

        foreach($productIds as $itemId) {

            WarehouseItem::firstOrCreate(
                [
                    "warehouse_id" => $warehouseId,
                    "item_id"      => $itemId
                ],
                [
                    "quantity"      => 0,
                    "minimum_stock" => 0,
                    "status"        => "active",
                    "created_at"    => now(),
                    "created_by"    => $userId
                ]
            );

        }

    }

}

