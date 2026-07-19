<?php

namespace App\Models\System\Sales;

use App\Helpers\System\Utilities;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Exception;

use App\Models\System\Organizations\{User};
use App\Models\System\Customers\{Customer};
use App\Models\System\General\{Currency};
use App\Models\System\Organizations\{Serie};
use App\Models\System\Finance\{CashSession};
use App\Models\System\Warehouses\{Warehouse};

class SaleHeader extends Model {

    protected $table               = "sales_header";
    protected $primaryKey          = "id";
    public $incrementing           = true;
    public $timestamps             = true;
    public static $snakeAttributes = true;

    protected $appends = [
        "hash_id",
        "serie_sequential",
        "formatted_issue_date",
        "diff_days_issue_date",
        "legible_total",
        "formatted_status",
        "formatted_delivery_mode",
        "formatted_delivery_status",
        "formatted_payment_modality",
        "formatted_payment_status"
    ];

    protected $fillable = [
        "company_id",
        "serie_id",
        "sequential",
        "holder_id",
        "seller_id",
        "currency_id",
            "warehouse_id",
            "cash_session_id",
            "quotation_header_id",
            "issue_date",
            "delivery_mode",
            "delivery_status",
            "delivered_at",
            "delivered_by",
            "delivery_observation",
            "payment_modality",
            "installment_extra_percentage",
            "installment_extra_amount",
            "subtotal",
            "tax",
            "commission_total",
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

    // Appends
    public function getHashIdAttribute() {

        return base64_encode((string) ($this->attributes["id"] ?? ""));

    }

    public function getSerieSequentialAttribute() {

        $serie_sequential = "";

        try {

            $serie_sequential = $this->serie->legible_serie."-".str_pad($this->sequential, 8, "0", STR_PAD_LEFT);

        }catch(Exception $e) {

            $serie_sequential = "Error";

        }

        return $serie_sequential;

    }

    public function getFormattedIssueDateAttribute() {

        $issueDate = $this->attributes["issue_date"] ?? null;

        return $issueDate ? Carbon::parse($issueDate)->format("d-m-Y") : "";

    }

    public function getDiffDaysIssueDateAttribute() {

        $issueDate = $this->attributes["issue_date"] ?? null;

        if(!$issueDate) return 0;

        $issueDateCarbon  = Carbon::parse($issueDate);
        $todayCarbon      = Carbon::now();
        $differenceInDays = $issueDateCarbon->diffInDays($todayCarbon);

        return $issueDateCarbon->isFuture() ? $differenceInDays : -$differenceInDays;

    }

    public function getLegibleTotalAttribute() {

        return Utilities::convertNumberToWords($this->attributes["total"] ?? 0);

    }

    public function getFormattedStatusAttribute() {

        return self::getStatuses("first", $this->attributes["status"] ?? "")["label"] ?? "";

    }

    public function getFormattedDeliveryModeAttribute() {

        return self::getDeliveryModes("first", $this->attributes["delivery_mode"] ?? "")["label"] ?? "";

    }

    public function getFormattedDeliveryStatusAttribute() {

        return self::getDeliveryStatuses("first", $this->attributes["delivery_status"] ?? "")["label"] ?? "";

    }

    public function getFormattedPaymentModalityAttribute() {

        return self::getPaymentModalities("first", $this->attributes["payment_modality"] ?? "")["label"] ?? "";

    }

    public function getFormattedPaymentStatusAttribute() {

        return self::getPaymentStatuses("first", $this->attributes["payment_status"] ?? "")["label"] ?? "";

    }

    // Functions
    public static function getStatuses($type = "all", $code = "") {

        $statuses = [
            ["code" => "active", "label" => "Activo"],
            ["code" => "inactive", "label" => "Inactivo"],
            ["code" => "canceled", "label" => "Anulado"]
        ];

        return Utilities::getValues($statuses, $type, $code);

    }

    public static function getDeliveryModes($type = "all", $code = "") {

        $modes = [
            ["code" => "immediate", "label" => "Entrega inmediata"],
            ["code" => "pending", "label" => "Entrega pendiente"]
        ];

        return Utilities::getValues($modes, $type, $code);

    }

    public static function getDeliveryStatuses($type = "all", $code = "") {

        $statuses = [
            ["code" => "pending", "label" => "Pendiente"],
            ["code" => "partial", "label" => "Parcial"],
            ["code" => "delivered", "label" => "Entregado"],
            ["code" => "canceled", "label" => "Anulado"]
        ];

        return Utilities::getValues($statuses, $type, $code);

    }

    public static function getPaymentModalities($type = "all", $code = "") {

        $modalities = [
            ["code" => "paid_now", "label" => "Pago al momento"],
            ["code" => "cash_on_delivery", "label" => "Contraentrega"],
            ["code" => "installments", "label" => "Pago por cuotas"]
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

    public static function getNewSequential($serie_id) {

        $newSequential = 0;

        try {

            $serie = Serie::query()
                          ->where("id", $serie_id)
                          ->lockForUpdate()
                          ->first(["id", "init"]);

            if(!Utilities::isDefined($serie)) {

                return 0;

            }

            $maxSequential = SaleHeader::where("serie_id", $serie_id)
                                       ->max("sequential");

            if(Utilities::isDefined($maxSequential)) {

                $newSequential = intval($maxSequential) + 1;

            }else {

                $newSequential = intval($serie->init);

            }

        }catch(Exception $e) {

            $newSequential = 0;

        }

        return $newSequential;

    }

    // Relationships
    public function serie() {

        return $this->belongsTo(Serie::class, "serie_id", "id");

    }

    public function holder() {

        return $this->belongsTo(Customer::class, "holder_id", "id");

    }

    public function seller() {

        return $this->belongsTo(User::class, "seller_id", "id");

    }

    public function currency() {

        return $this->belongsTo(Currency::class, "currency_id", "id");

    }

    public function warehouse() {

        return $this->belongsTo(Warehouse::class, "warehouse_id", "id");

    }

    public function cashSession() {

        return $this->belongsTo(CashSession::class, "cash_session_id", "id");

    }

    public function quotation() {

        return $this->belongsTo(QuotationHeader::class, "quotation_header_id", "id");

    }

    public function deliveredBy() {

        return $this->belongsTo(User::class, "delivered_by", "id");

    }

    public function positions() {

        return $this->hasMany(SaleBody::class, "sale_header_id", "id")
                    ->whereIn("status", ["active"]);

    }

    public function allPositions() {

        return $this->hasMany(SaleBody::class, "sale_header_id", "id");

    }

    public function taxes() {

        return $this->hasMany(SaleTax::class, "sale_header_id", "id")
                    ->whereIn("status", ["active"]);

    }

    public function payments() {

        return $this->hasMany(SalePayment::class, "sale_header_id", "id")
                    ->whereIn("status", ["active"]);

    }

    public function accountReceivable() {

        return $this->hasOne(SaleAccountReceivable::class, "sale_header_id", "id");

    }

}
