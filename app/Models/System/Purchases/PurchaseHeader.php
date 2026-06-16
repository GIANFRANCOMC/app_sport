<?php

declare(strict_types=1);

namespace App\Models\System\Purchases;

use Illuminate\Database\Eloquent\Model;

use App\Models\System\General\Currency;
use App\Models\System\Warehouses\Warehouse;

final class PurchaseHeader extends Model {

    protected $table = "purchase_headers";

    protected $fillable = [
        "company_id",
        "supplier_id",
        "warehouse_id",
        "currency_id",
        "document_type",
        "document_number",
        "issue_date",
        "expected_date",
        "subtotal",
        "tax",
        "total",
        "observation",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
        "canceled_at",
        "canceled_by"
    ];

    protected $casts = [
        "issue_date" => "date:Y-m-d",
        "expected_date" => "date:Y-m-d",
        "subtotal" => "decimal:2",
        "tax" => "decimal:2",
        "total" => "decimal:2",
        "canceled_at" => "datetime"
    ];

    protected $appends = ["formatted_status", "formatted_document_type", "receipt_progress"];

    public function getFormattedStatusAttribute(): string {

        return [
            "confirmed" => "Pendiente de recepción",
            "partial" => "Recepción parcial",
            "received" => "Recibida",
            "canceled" => "Anulada"
        ][$this->attributes["status"] ?? ""] ?? "";

    }

    public function getFormattedDocumentTypeAttribute(): string {

        return ($this->attributes["document_type"] ?? "") === "invoice"
            ? "Factura de compra"
            : "Orden de compra";

    }

    public function getReceiptProgressAttribute(): float {

        if(!$this->relationLoaded("items")) return 0;

        $ordered = (float) $this->items->sum("quantity");
        $received = (float) $this->items->sum("received_quantity");

        return $ordered > 0 ? round(($received / $ordered) * 100, 2) : 0;

    }

    public function supplier() {

        return $this->belongsTo(Supplier::class, "supplier_id");

    }

    public function warehouse() {

        return $this->belongsTo(Warehouse::class, "warehouse_id");

    }

    public function currency() {

        return $this->belongsTo(Currency::class, "currency_id");

    }

    public function items() {

        return $this->hasMany(PurchaseItem::class, "purchase_header_id");

    }

    public function receipts() {

        return $this->hasMany(PurchaseReceipt::class, "purchase_header_id");

    }

    public function taxes() {

        return $this->hasMany(PurchaseTax::class, "purchase_header_id")
                    ->whereIn("status", ["active"]);

    }

    public function payments() {

        return $this->hasMany(PurchasePayment::class, "purchase_header_id")
                    ->whereIn("status", ["active"]);

    }

}
