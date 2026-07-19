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
        "reference",
        "document_number",
        "issue_date",
        "expected_date",
        "due_date",
        "approval_status",
        "approved_by",
        "approved_at",
        "delivery_mode",
        "payment_modality",
        "installment_extra_percentage",
        "installment_extra_amount",
        "subtotal",
        "tax",
        "expense_total",
        "total",
        "paid_amount",
        "balance_due",
        "payment_status",
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
        "due_date" => "date:Y-m-d",
        "approved_at" => "datetime",
        "subtotal" => "decimal:4",
        "tax" => "decimal:4",
        "installment_extra_percentage" => "decimal:4",
        "installment_extra_amount" => "decimal:4",
        "expense_total" => "decimal:4",
        "total" => "decimal:4",
        "paid_amount" => "decimal:4",
        "balance_due" => "decimal:4",
        "canceled_at" => "datetime"
    ];

    protected $appends = ["formatted_status", "formatted_document_type", "formatted_delivery_mode", "formatted_payment_modality", "formatted_payment_status", "receipt_progress"];

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


    public function getFormattedDeliveryModeAttribute(): string {

        return [
            "immediate" => "Entrega inmediata",
            "pending" => "Entrega pendiente"
        ][$this->attributes["delivery_mode"] ?? ""] ?? "";

    }

    public function getFormattedPaymentModalityAttribute(): string {

        return [
            "paid_now" => "Pago al momento",
            "cash_on_delivery" => "Contraentrega",
            "installments" => "Pago por cuotas"
        ][$this->attributes["payment_modality"] ?? ""] ?? "";

    }

    public function getFormattedPaymentStatusAttribute(): string {

        return [
            "unpaid" => "Pendiente",
            "partial" => "Parcial",
            "paid" => "Pagado",
            "overpaid" => "Sobrepagado"
        ][$this->attributes["payment_status"] ?? ""] ?? "";

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

    public function accountPayable() {

        return $this->hasOne(PurchaseAccountPayable::class, "purchase_header_id");

    }

    public function expenses() {

        return $this->hasMany(PurchaseExpense::class, "purchase_header_id");

    }

    public function returns() {

        return $this->hasMany(PurchaseReturn::class, "purchase_header_id");

    }

}
