<?php

declare(strict_types=1);

namespace App\Models\System\Purchases;

use Illuminate\Database\Eloquent\Model;

use App\Helpers\System\Utilities;
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

        return self::getStatuses("first", $this->attributes["status"] ?? "")["label"] ?? "";

    }

    public function getFormattedDocumentTypeAttribute(): string {

        return self::getDocumentTypes("first", $this->attributes["document_type"] ?? "")["label"] ?? "";

    }


    public function getFormattedDeliveryModeAttribute(): string {

        return self::getDeliveryModes("first", $this->attributes["delivery_mode"] ?? "")["label"] ?? "";

    }

    public function getFormattedPaymentModalityAttribute(): string {

        return self::getPaymentModalities("first", $this->attributes["payment_modality"] ?? "")["label"] ?? "";

    }

    public function getFormattedPaymentStatusAttribute(): string {

        return self::getPaymentStatuses("first", $this->attributes["payment_status"] ?? "")["label"] ?? "";

    }

    public static function getStatuses($type = "all", $code = "") {

        $statuses = [
            ["code" => "confirmed", "label" => "Pendiente de recepción"],
            ["code" => "partial", "label" => "Recepción parcial"],
            ["code" => "received", "label" => "Recibida"],
            ["code" => "canceled", "label" => "Anulada"]
        ];

        return Utilities::getValues($statuses, $type, $code);

    }

    public static function getDocumentTypes($type = "all", $code = "") {

        $types = [
            ["code" => "order", "label" => "Orden de compra"],
            ["code" => "invoice", "label" => "Factura de compra"]
        ];

        return Utilities::getValues($types, $type, $code);

    }

    public static function getDeliveryModes($type = "all", $code = "") {

        $modes = [
            ["code" => "immediate", "label" => "Recepción inmediata"],
            ["code" => "pending", "label" => "Recepción parcial o pendiente"]
        ];

        return Utilities::getValues($modes, $type, $code);

    }

    public static function getPaymentModalities($type = "all", $code = "") {

        $modalities = [
            ["code" => "paid_now", "label" => "Pago al momento"],
            ["code" => "cash_on_delivery", "label" => "Pago contra entrega"],
            ["code" => "installments", "label" => "Pago en cuotas"]
        ];

        return Utilities::getValues($modalities, $type, $code);

    }

    public static function getPaymentStatuses($type = "all", $code = "") {

        $statuses = [
            ["code" => "unpaid", "label" => "Pendiente"],
            ["code" => "partial", "label" => "Parcial"],
            ["code" => "paid", "label" => "Pagado"],
            ["code" => "overpaid", "label" => "Sobrepagado"]
        ];

        return Utilities::getValues($statuses, $type, $code);

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
