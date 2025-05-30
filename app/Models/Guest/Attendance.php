<?php

namespace App\Models\Guest;

use App\Helpers\System\Utilities;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model {

    protected $table               = "attendances";
    protected $primaryKey          = "id";
    public $incrementing           = true;
    public $timestamps             = true;
    public static $snakeAttributes = true;

    protected $appends = [
        "formatted_type",
        "formatted_status"
    ];

    protected $fillable = [
        "company_id",
        "branch_id",
        "customer_id",
        "start_date",
        "end_date",
        "observation",
        "type",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
        "canceled_at",
        "canceled_by"
    ];

    // Appends
    public function getFormattedTypeAttribute() {

        return self::getTypes("first", $this->attributes["type"])["label"] ?? "";

    }

    public function getFormattedStatusAttribute() {

        return self::getStatuses("first", $this->attributes["status"])["label"] ?? "";

    }

    // Functions
    public static function getTypes($type = "all", $code = "") {

        $types = [
            ["code" => "form_manual", "label" => "Formulario Manual"],
            ["code" => "qr_manual", "label" => "QR manual"],
            ["code" => "qr_automatic", "label" => "QR automático"]
        ];

        return Utilities::getValues($types, $type, $code);

    }

    public static function getStatuses($type = "all", $code = "") {

        $statuses = [
            ["code" => "active", "label" => "En curso"],
            ["code" => "canceled", "label" => "Anulada"],
            ["code" => "inactive", "label" => "Inactiva"],
            ["code" => "finalized", "label" => "Concluida"]
        ];

        return Utilities::getValues($statuses, $type, $code);

    }

    // Relationships
    public function company() {

        return $this->belongsTo(Company::class, "company_id", "id");

    }

    public function branch() {

        return $this->belongsTo(Branch::class, "branch_id", "id");

    }

    public function customer() {

        return $this->belongsTo(Customer::class, "customer_id", "id");

    }

}
