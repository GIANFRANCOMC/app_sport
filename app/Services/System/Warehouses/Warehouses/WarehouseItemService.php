<?php

declare(strict_types=1);

namespace App\Services\System\Warehouses\Warehouses;

use App\Models\System\Catalogs\Item;
use App\Models\System\Settings\Branch;
use App\Models\System\Warehouses\WarehouseItem;

class WarehouseItemService {

    /**
     * Crea los WarehouseItem iniciales para un producto en todos los almacenes de la empresa.
     */
    public static function createForProductInAllWarehouses(Item $item, int $companyId, int $userId): void {

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

}


