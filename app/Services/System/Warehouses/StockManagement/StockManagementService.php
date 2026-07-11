<?php

declare(strict_types=1);

namespace App\Services\System\Warehouses\StockManagement;

use App\Helpers\System\Utilities;
use App\Models\System\Catalogs\Item;
use App\Models\System\Warehouses\{InventoryStockAlert, Warehouse, WarehouseItem};
use App\Services\System\Warehouses\Inventory\InventoryMovementService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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
    public static function getPaginatedList(
        int $companyId,
        int $warehouseId,
        int $perPage,
        string $search = ""
    ) {

        return self::stockQuery($companyId, $warehouseId, $search)->paginate($perPage);

    }

    public static function getStockReport(
        int $companyId,
        int $warehouseId,
        string $search = ""
    ): Collection {

        return self::stockQuery($companyId, $warehouseId, $search)->get();

    }

    public static function getConsolidatedStock(
        int $companyId,
        string $search = "",
        ?array $allowedWarehouseIds = null
    ): Collection {

        return Item::query()
            ->where("company_id", $companyId)
            ->where("type", "product")
            ->with([
                "warehouseItems" => function($query) use($allowedWarehouseIds) {
                    $query->with(["warehouse.branch:id,name"]);

                    if($allowedWarehouseIds !== null) {
                        $query->whereIn("warehouse_id", $allowedWarehouseIds);
                    }
                }
            ])
            ->when(trim($search) !== "", function($query) use($search) {
                $query->where(function($query) use($search) {
                    $query->where("name", "like", "%{$search}%")
                        ->orWhere("internal_code", "like", "%{$search}%")
                        ->orWhere("barcode", "like", "%{$search}%");
                });
            })
            ->orderBy("name")
            ->get()
            ->map(function(Item $item) {
                $warehouses = $item->warehouseItems->map(function(WarehouseItem $warehouseItem) {
                    $quantity = (float) ($warehouseItem->quantity ?? 0);
                    $minimum = (float) ($warehouseItem->minimum_stock ?? 0);

                    return [
                        "warehouse_id" => (int) $warehouseItem->warehouse_id,
                        "warehouse_name" => $warehouseItem->warehouse?->name,
                        "branch_name" => $warehouseItem->warehouse?->branch?->name,
                        "quantity" => $quantity,
                        "minimum_stock" => $minimum,
                        "requires_attention" => $quantity <= $minimum
                    ];
                })->values();

                $item->setAttribute("stock_quantity", round((float) $warehouses->sum("quantity"), 4));
                $item->setAttribute("minimum_stock", round((float) $warehouses->sum("minimum_stock"), 4));
                $item->setAttribute("warehouse_breakdown", $warehouses);
                $item->setAttribute("alert_warehouses_count", $warehouses->where("requires_attention", true)->count());

                return $item;
            });

    }

    private static function stockQuery(
        int $companyId,
        int $warehouseId,
        string $search = ""
    ): Builder {

        $query = Item::query()
            ->where("company_id", $companyId)
            ->where("type", "product")
            ->withSum(["warehouseItems as stock_quantity" => function($query) use($warehouseId) {

                $query->where("warehouse_id", $warehouseId);

            }], "quantity")
            ->withSum(["warehouseItems as minimum_stock" => function($query) use($warehouseId) {

                $query->where("warehouse_id", $warehouseId);

            }], "minimum_stock");

        $search = trim($search);

        if($search !== "") {

            $query->where(function($query) use($search) {

                $query->where("name", "like", "%{$search}%")
                    ->orWhere("internal_code", "like", "%{$search}%")
                    ->orWhere("barcode", "like", "%{$search}%");

            });

        }

        return $query->orderBy("name");

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
                    "origin_type"      => InventoryMovementService::ORIGIN_PHYSICAL_COUNT,
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
        string $originType = InventoryMovementService::ORIGIN_MANUAL,
        ?int $userId = null,
        ?float $unitCost = null
    ) {

        return InventoryMovementService::apply([
            "company_id"       => $companyId,
            "warehouse_id"     => $warehouseId,
            "item_id"          => $itemId,
            "user_id"          => $userId,
            "movement_type"    => $movementType,
            "origin_type"      => $originType,
            "quantity"         => $quantity,
            "resulting_balance" => $resultingBalance,
            "unit_cost"        => $unitCost,
            "reason"           => $reason
        ]);

    }

    public static function createManualMovements(
        int $companyId,
        int $warehouseId,
        string $movementType,
        string $originType,
        array $items,
        string $reason,
        ?int $userId = null
    ): array {

        return DB::transaction(function() use(
            $companyId,
            $warehouseId,
            $movementType,
            $originType,
            $items,
            $reason,
            $userId
        ) {

            $movements = [];

            foreach($items as $item) {

                $movements[] = self::createManualMovement(
                    $companyId,
                    $warehouseId,
                    (int) $item["item_id"],
                    $movementType,
                    isset($item["quantity"]) ? (float) $item["quantity"] : null,
                    isset($item["resulting_balance"])
                        ? (float) $item["resulting_balance"]
                        : null,
                    $reason,
                    $originType,
                    $userId,
                    isset($item["unit_cost"]) && $item["unit_cost"] !== ""
                        ? (float) $item["unit_cost"]
                        : null
                );

            }

            return $movements;

        });

    }

    public static function getKardex(int $companyId, array $filters, int $perPage) {

        return InventoryMovementService::getPaginatedKardex($companyId, $filters, $perPage);

    }

    public static function getKardexReport(int $companyId, array $filters): Collection {

        return InventoryMovementService::getKardexQuery($companyId, $filters)
            ->orderByDesc("id")
            ->get();

    }

    public static function transfer(array $data) {

        return InventoryMovementService::transfer($data);

    }

    public static function getStockAlerts(
        int $companyId,
        array $filters,
        int $perPage
    ) {

        return InventoryStockAlert::query()
            ->where("company_id", $companyId)
            ->with([
                "warehouseItem.warehouse.branch:id,name",
                "warehouseItem.item:id,internal_code,barcode,name",
                "resolvedBy:id,name"
            ])
            ->when($filters["warehouse_id"] ?? null, fn($query, $warehouseId) =>
                $query->whereHas("warehouseItem", fn($warehouseItem) =>
                    $warehouseItem->where("warehouse_id", $warehouseId)
                )
            )
            ->when($filters["status"] ?? null, fn($query, $status) => $query->where("status", $status))
            ->orderByDesc("detected_at")
            ->paginate($perPage);

    }

}
