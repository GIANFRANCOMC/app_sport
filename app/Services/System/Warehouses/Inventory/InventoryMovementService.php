<?php

declare(strict_types=1);

namespace App\Services\System\Warehouses\Inventory;

use DomainException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\System\Catalogs\Item;
use App\Models\System\Warehouses\{InventoryMovement, Warehouse, WarehouseItem};

final class InventoryMovementService {

    public const TYPE_ENTRY      = "entry";
    public const TYPE_EXIT       = "exit";
    public const TYPE_CORRECTION = "correction";

    public const ORIGIN_PRODUCT_OPENING      = "product_opening";
    public const ORIGIN_MANUAL               = "manual";
    public const ORIGIN_SALE                 = "sale";
    public const ORIGIN_SALE_CANCELLATION    = "sale_cancellation";
    public const ORIGIN_PURCHASE             = "purchase";
    public const ORIGIN_PURCHASE_CANCELLATION = "purchase_cancellation";
    public const ORIGIN_TRANSFER_OUT         = "transfer_out";
    public const ORIGIN_TRANSFER_IN          = "transfer_in";

    private const MOVEMENT_TYPES = [
        self::TYPE_ENTRY,
        self::TYPE_EXIT,
        self::TYPE_CORRECTION
    ];

    public static function apply(array $data): InventoryMovement {

        return DB::transaction(function() use($data) {

            $companyId   = (int) ($data["company_id"] ?? 0);
            $warehouseId = (int) ($data["warehouse_id"] ?? 0);
            $itemId      = (int) ($data["item_id"] ?? 0);
            $type        = (string) ($data["movement_type"] ?? "");
            $originType  = trim((string) ($data["origin_type"] ?? ""));
            $reason      = trim((string) ($data["reason"] ?? ""));

            if(!in_array($type, self::MOVEMENT_TYPES, true)) {

                throw new DomainException("El tipo de movimiento de inventario no es válido.");

            }

            if($originType === "" || $reason === "") {

                throw new DomainException("El origen y el motivo del movimiento son obligatorios.");

            }

            self::assertWarehouseAndItemBelongToCompany($warehouseId, $itemId, $companyId);

            $warehouseItem = WarehouseItem::where("warehouse_id", $warehouseId)
                ->where("item_id", $itemId)
                ->lockForUpdate()
                ->first();

            if(!$warehouseItem) {

                WarehouseItem::create([
                    "warehouse_id" => $warehouseId,
                    "item_id"      => $itemId,
                    "quantity"     => 0,
                    "minimum_stock" => 0,
                    "status"       => "active",
                    "created_at"   => now(),
                    "created_by"   => $data["user_id"] ?? null
                ]);

                $warehouseItem = WarehouseItem::where("warehouse_id", $warehouseId)
                    ->where("item_id", $itemId)
                    ->lockForUpdate()
                    ->firstOrFail();

            }

            $quantityBefore = round((float) $warehouseItem->quantity, 2);
            $quantityChange = self::resolveQuantityChange($type, $quantityBefore, $data);
            $quantityAfter  = round($quantityBefore + $quantityChange, 2);

            if(abs($quantityChange) < 0.00001) {

                throw new DomainException("El movimiento no modifica el saldo actual.");

            }

            if($quantityAfter < 0 && !($data["allow_negative"] ?? false)) {

                throw new DomainException("La salida supera el stock disponible en el almacén.");

            }

            $warehouseItem->update([
                "quantity"   => $quantityAfter,
                "status"     => "active",
                "updated_at" => now(),
                "updated_by" => $data["user_id"] ?? null
            ]);

            $metadata = $data["metadata"] ?? [];

            if(!empty($data["reference"])) {

                $metadata["reference"] = $data["reference"];

            }

            return InventoryMovement::create([
                "company_id"      => $companyId,
                "warehouse_id"    => $warehouseId,
                "item_id"         => $itemId,
                "user_id"         => $data["user_id"] ?? null,
                "movement_type"   => $type,
                "origin_type"     => $originType,
                "origin_id"       => $data["origin_id"] ?? null,
                "quantity_before" => $quantityBefore,
                "quantity_change" => $quantityChange,
                "quantity_after"  => $quantityAfter,
                "reason"          => $reason,
                "metadata"        => $metadata ?: null,
                "created_at"      => now()
            ]);

        });

    }

    public static function transfer(array $data): array {

        return DB::transaction(function() use($data) {

            $companyId = (int) ($data["company_id"] ?? 0);
            $sourceWarehouseId = (int) ($data["source_warehouse_id"] ?? 0);
            $destinationWarehouseId = (int) ($data["destination_warehouse_id"] ?? 0);
            $items = is_array($data["items"] ?? null) ? $data["items"] : [];
            $reason = trim((string) ($data["reason"] ?? ""));

            if($sourceWarehouseId === $destinationWarehouseId) {

                throw new DomainException("Selecciona almacenes diferentes para el traslado.");

            }

            if(empty($items)) {

                throw new DomainException("Agrega al menos un producto al traslado.");

            }

            if(count($items) > 100) {

                throw new DomainException("Puedes trasladar hasta 100 productos por operación.");

            }

            if($reason === "") {

                throw new DomainException("El motivo del traslado es obligatorio.");

            }

            $reference = "TRF-" . strtoupper(Str::random(12));
            $movements = [];
            $processedItemIds = [];

            foreach($items as $item) {

                $itemId = (int) ($item["item_id"] ?? 0);
                $quantity = round((float) ($item["quantity"] ?? 0), 2);

                if($quantity <= 0) {

                    throw new DomainException("Todas las cantidades deben ser mayores que cero.");

                }

                if(in_array($itemId, $processedItemIds, true)) {

                    throw new DomainException("No repitas un producto en el mismo traslado.");

                }

                $processedItemIds[] = $itemId;

                self::assertWarehouseAndItemBelongToCompany($sourceWarehouseId, $itemId, $companyId);
                self::assertWarehouseAndItemBelongToCompany($destinationWarehouseId, $itemId, $companyId);

                $metadata = [
                    "reference"                 => $reference,
                    "source_warehouse_id"      => $sourceWarehouseId,
                    "destination_warehouse_id" => $destinationWarehouseId
                ];

                $exit = self::apply([
                    "company_id"     => $companyId,
                    "warehouse_id"   => $sourceWarehouseId,
                    "item_id"        => $itemId,
                    "user_id"        => $data["user_id"] ?? null,
                    "movement_type"  => self::TYPE_EXIT,
                    "origin_type"    => self::ORIGIN_TRANSFER_OUT,
                    "quantity"       => $quantity,
                    "reason"         => $reason,
                    "reference"      => $reference,
                    "metadata"       => $metadata
                ]);

                $entry = self::apply([
                    "company_id"     => $companyId,
                    "warehouse_id"   => $destinationWarehouseId,
                    "item_id"        => $itemId,
                    "user_id"        => $data["user_id"] ?? null,
                    "movement_type"  => self::TYPE_ENTRY,
                    "origin_type"    => self::ORIGIN_TRANSFER_IN,
                    "quantity"       => $quantity,
                    "reason"         => $reason,
                    "reference"      => $reference,
                    "metadata"       => $metadata
                ]);

                $movements[] = [
                    "item_id" => $itemId,
                    "exit"    => $exit,
                    "entry"   => $entry
                ];

            }

            return [
                "reference"   => $reference,
                "items_count" => count($movements),
                "movements"   => $movements
            ];

        });

    }

    public static function getPaginatedKardex(
        int $companyId,
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {

        $query = InventoryMovement::query()
            ->where("company_id", $companyId)
            ->with([
                "warehouse.branch:id,name,status",
                "item:id,internal_code,name",
                "user:id,name"
            ]);

        if(!empty($filters["warehouse_id"])) {

            $query->where("warehouse_id", (int) $filters["warehouse_id"]);

        }

        if(!empty($filters["item_id"])) {

            $query->where("item_id", (int) $filters["item_id"]);

        }

        if(!empty($filters["movement_type"])) {

            $query->where("movement_type", $filters["movement_type"]);

        }

        if(!empty($filters["date_from"])) {

            $query->whereDate("created_at", ">=", $filters["date_from"]);

        }

        if(!empty($filters["date_to"])) {

            $query->whereDate("created_at", "<=", $filters["date_to"]);

        }

        return $query->orderByDesc("id")->paginate($perPage);

    }

    private static function resolveQuantityChange(
        string $type,
        float $quantityBefore,
        array $data
    ): float {

        if($type === self::TYPE_CORRECTION) {

            if(!array_key_exists("resulting_balance", $data)) {

                throw new DomainException("Debes indicar el saldo físico resultante de la corrección.");

            }

            $resultingBalance = round((float) $data["resulting_balance"], 2);

            if($resultingBalance < 0) {

                throw new DomainException("El saldo corregido no puede ser negativo.");

            }

            return round($resultingBalance - $quantityBefore, 2);

        }

        $quantity = round((float) ($data["quantity"] ?? 0), 2);

        if($quantity <= 0) {

            throw new DomainException("La cantidad debe ser mayor que cero.");

        }

        return $type === self::TYPE_ENTRY ? $quantity : -$quantity;

    }

    private static function assertWarehouseAndItemBelongToCompany(
        int $warehouseId,
        int $itemId,
        int $companyId
    ): void {

        $warehouseExists = Warehouse::whereKey($warehouseId)
            ->whereHas("branch", fn($query) => $query->where("company_id", $companyId))
            ->exists();

        $itemExists = Item::whereKey($itemId)
            ->where("company_id", $companyId)
            ->where("type", "product")
            ->exists();

        if(!$warehouseExists || !$itemExists) {

            throw new DomainException("El producto o el almacén no pertenece a la empresa.");

        }

    }

}
