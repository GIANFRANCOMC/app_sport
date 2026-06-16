<?php

declare(strict_types=1);

namespace App\Services\System\Purchases;

use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\System\Catalogs\Item;
use App\Models\System\Purchases\{
    PurchaseHeader,
    PurchaseItem,
    PurchasePayment,
    PurchaseReceipt,
    PurchaseReceiptItem,
    PurchaseTax,
    Supplier
};
use App\Services\System\Finance\CommercialDocumentSettlementService;
use App\Services\System\Warehouses\Inventory\InventoryMovementService;
use App\Services\System\Warehouses\StockManagement\StockManagementService;

final class PurchaseService {

    public static function getFilteredQuery(int $companyId, array $filters = []): Builder {

        $query = PurchaseHeader::query()
            ->where("company_id", $companyId)
            ->with([
                "supplier:id,name,document_number",
                "warehouse:id,name",
                "currency:id,code,sign",
                "items.item:id,internal_code,barcode,name",
                "taxes",
                "payments"
            ]);

        $word = trim((string) ($filters["word"] ?? ""));

        if($word !== "") {

            $query->where(function($query) use($word) {

                $query->where("document_number", "like", "%{$word}%")
                    ->orWhereHas("supplier", fn($query) =>
                        $query->where("name", "like", "%{$word}%")
                            ->orWhere("document_number", "like", "%{$word}%")
                    )
                    ->orWhereHas("items.item", fn($query) =>
                        $query->where("name", "like", "%{$word}%")
                            ->orWhere("internal_code", "like", "%{$word}%")
                            ->orWhere("barcode", "like", "%{$word}%")
                    );

            });

        }

        if(!empty($filters["status"])) {

            $query->where("status", $filters["status"]);

        }

        return $query->orderByDesc("id");

    }

    public static function create(
        int $companyId,
        int $userId,
        array $data
    ): PurchaseHeader {

        return DB::transaction(function() use($companyId, $userId, $data) {

            $supplier = Supplier::query()
                ->where("company_id", $companyId)
                ->whereKey((int) $data["supplier_id"])
                ->firstOrFail();
            $warehouse = StockManagementService::validateWarehouse(
                (int) $data["warehouse_id"],
                $companyId
            );

            if(!$warehouse || $supplier->status !== "active") {

                throw new DomainException("El proveedor o el almacén no está disponible.");

            }

            $documentNumber = trim((string) ($data["document_number"] ?? ""));

            if($documentNumber !== "" && PurchaseHeader::query()
                ->where("company_id", $companyId)
                ->where("supplier_id", $supplier->id)
                ->where("document_type", $data["document_type"])
                ->where("document_number", $documentNumber)
                ->where("status", "!=", "canceled")
                ->exists()) {

                throw new DomainException(
                    "Ya existe un documento de compra activo con ese número para el proveedor."
                );

            }

            $itemIds = collect($data["items"])->pluck("item_id")->map(fn($id) => (int) $id);
            $items = Item::query()
                ->where("company_id", $companyId)
                ->where("type", "product")
                ->whereIn("id", $itemIds)
                ->get()
                ->keyBy("id");

            if($items->count() !== $itemIds->unique()->count()) {

                throw new DomainException("Uno de los productos no pertenece a la empresa.");

            }

            $subtotal = collect($data["items"])->sum(fn($item) =>
                round((float) $item["quantity"] * (float) $item["unit_cost"], 2)
            );
            $taxLines = CommercialDocumentSettlementService::taxes(
                $companyId,
                "purchase",
                (float) $subtotal,
                $data["taxes"] ?? [],
                $userId
            );
            $tax = round((float) $taxLines->sum("amount"), 2);
            $total = round($subtotal + $tax, 2);
            $paymentLines = CommercialDocumentSettlementService::payments(
                $companyId,
                "purchase",
                (float) $total,
                $data["payments"] ?? [],
                $userId
            );

            $purchase = PurchaseHeader::create([
                "company_id" => $companyId,
                "supplier_id" => $supplier->id,
                "warehouse_id" => $warehouse->id,
                "currency_id" => (int) $data["currency_id"],
                "document_type" => $data["document_type"],
                "document_number" => $documentNumber ?: null,
                "issue_date" => $data["issue_date"],
                "expected_date" => $data["expected_date"] ?? null,
                "subtotal" => $subtotal,
                "tax" => $tax,
                "total" => $total,
                "observation" => $data["observation"] ?? null,
                "status" => "confirmed",
                "created_at" => now(),
                "created_by" => $userId
            ]);

            if($taxLines->isNotEmpty()) {

                PurchaseTax::insert($taxLines
                    ->map(fn($tax) => ["purchase_header_id" => $purchase->id] + $tax)
                    ->all());

            }

            if($paymentLines->isNotEmpty()) {

                PurchasePayment::insert($paymentLines
                    ->map(fn($payment) => ["purchase_header_id" => $purchase->id] + $payment)
                    ->all());

            }

            foreach($data["items"] as $detail) {

                $item = $items->get((int) $detail["item_id"]);
                $quantity = round((float) $detail["quantity"], 2);
                $unitCost = round((float) $detail["unit_cost"], 4);

                PurchaseItem::create([
                    "purchase_header_id" => $purchase->id,
                    "item_id" => $item->id,
                    "name" => $item->name,
                    "quantity" => $quantity,
                    "received_quantity" => 0,
                    "unit_cost" => $unitCost,
                    "subtotal" => round($quantity * $unitCost, 2),
                    "status" => "pending",
                    "created_at" => now(),
                    "created_by" => $userId
                ]);

            }

            return self::find($companyId, $purchase->id);

        });

    }

    public static function receive(
        int $companyId,
        int $purchaseId,
        int $userId,
        array $data
    ): PurchaseReceipt {

        return DB::transaction(function() use($companyId, $purchaseId, $userId, $data) {

            $purchase = PurchaseHeader::query()
                ->where("company_id", $companyId)
                ->whereKey($purchaseId)
                ->lockForUpdate()
                ->firstOrFail();

            if(!in_array($purchase->status, ["confirmed", "partial"], true)) {

                throw new DomainException("La compra no admite nuevas recepciones.");

            }

            $purchase->load("items");
            $receipt = PurchaseReceipt::create([
                "purchase_header_id" => $purchase->id,
                "warehouse_id" => $purchase->warehouse_id,
                "reference" => "REC-" . strtoupper(Str::random(10)),
                "received_at" => $data["received_at"],
                "observation" => $data["observation"] ?? null,
                "status" => "received",
                "created_at" => now(),
                "created_by" => $userId
            ]);

            foreach($data["items"] as $receivedItem) {

                $purchaseItem = $purchase->items
                    ->firstWhere("id", (int) $receivedItem["purchase_item_id"]);

                if(!$purchaseItem) {

                    throw new DomainException("Uno de los productos no pertenece a la compra.");

                }

                $quantity = round((float) $receivedItem["quantity"], 2);
                $remaining = round(
                    (float) $purchaseItem->quantity - (float) $purchaseItem->received_quantity,
                    2
                );

                if($quantity <= 0 || $quantity > $remaining) {

                    throw new DomainException(
                        "La cantidad recibida de {$purchaseItem->name} supera el saldo pendiente."
                    );

                }

                $movement = InventoryMovementService::apply([
                    "company_id" => $companyId,
                    "warehouse_id" => (int) $purchase->warehouse_id,
                    "item_id" => (int) $purchaseItem->item_id,
                    "user_id" => $userId,
                    "movement_type" => InventoryMovementService::TYPE_ENTRY,
                    "origin_type" => InventoryMovementService::ORIGIN_PURCHASE,
                    "origin_id" => (int) $receipt->id,
                    "quantity" => $quantity,
                    "unit_cost" => (float) $purchaseItem->unit_cost,
                    "reason" => "Recepción de compra.",
                    "reference" => $receipt->reference,
                    "metadata" => [
                        "purchase_header_id" => (int) $purchase->id,
                        "purchase_item_id" => (int) $purchaseItem->id
                    ]
                ]);

                PurchaseReceiptItem::create([
                    "purchase_receipt_id" => $receipt->id,
                    "purchase_item_id" => $purchaseItem->id,
                    "item_id" => $purchaseItem->item_id,
                    "inventory_movement_id" => $movement->id,
                    "quantity" => $quantity,
                    "unit_cost" => $purchaseItem->unit_cost,
                    "total_cost" => round($quantity * (float) $purchaseItem->unit_cost, 2),
                    "created_at" => now(),
                    "created_by" => $userId
                ]);

                $receivedQuantity = round(
                    (float) $purchaseItem->received_quantity + $quantity,
                    2
                );
                $purchaseItem->update([
                    "received_quantity" => $receivedQuantity,
                    "status" => $receivedQuantity >= (float) $purchaseItem->quantity
                        ? "received"
                        : "partial",
                    "updated_at" => now(),
                    "updated_by" => $userId
                ]);

            }

            $purchase->load("items");
            $allReceived = $purchase->items->every(fn($item) =>
                (float) $item->received_quantity >= (float) $item->quantity
            );
            $purchase->update([
                "status" => $allReceived ? "received" : "partial",
                "updated_at" => now(),
                "updated_by" => $userId
            ]);

            return $receipt->load("items.item");

        });

    }

    public static function cancel(int $companyId, int $purchaseId, int $userId): PurchaseHeader {

        return DB::transaction(function() use($companyId, $purchaseId, $userId) {

            $purchase = PurchaseHeader::query()
                ->where("company_id", $companyId)
                ->whereKey($purchaseId)
                ->with("items")
                ->lockForUpdate()
                ->firstOrFail();

            if($purchase->items->contains(fn($item) => (float) $item->received_quantity > 0)) {

                throw new DomainException(
                    "La compra tiene mercadería recibida. Registra una devolución a proveedor desde Inventario."
                );

            }

            if($purchase->status === "canceled") {

                throw new DomainException("La compra ya está anulada.");

            }

            $purchase->update([
                "status" => "canceled",
                "updated_at" => now(),
                "updated_by" => $userId,
                "canceled_at" => now(),
                "canceled_by" => $userId
            ]);
            PurchaseItem::where("purchase_header_id", $purchase->id)->update([
                "status" => "canceled",
                "updated_at" => now(),
                "updated_by" => $userId
            ]);

            return self::find($companyId, $purchase->id);

        });

    }

    public static function find(int $companyId, int $purchaseId): PurchaseHeader {

        return PurchaseHeader::query()
            ->where("company_id", $companyId)
            ->with([
                "supplier",
                "warehouse.branch",
                "currency",
                "items.item",
                "taxes",
                "payments",
                "receipts.items.item"
            ])
            ->findOrFail($purchaseId);

    }

}
