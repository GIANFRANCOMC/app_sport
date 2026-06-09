<?php

namespace App\Models\System\Assets;

use App\Helpers\System\Utilities;
use Illuminate\Database\Eloquent\Model;

use App\Models\System\Organizations\{Company};

class Asset extends Model {

    protected $table               = "assets";
    protected $primaryKey          = "id";
    public $incrementing           = true;
    public $timestamps             = true;
    public static $snakeAttributes = true;

    protected $appends = [
        "formatted_management_type",
        "formatted_status"
    ];

    protected $fillable = [
        "company_id",
        "internal_code",
        "name",
        "description",
        "management_type",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    // Appends
    public function getFormattedManagementTypeAttribute() {

        return self::getManagementTypes("first", $this->attributes["management_type"] ?? "")["label"] ?? "";

    }

    public function getFormattedStatusAttribute() {

        return self::getStatuses("first", $this->attributes["status"] ?? "")["label"] ?? "";

    }

    // Functions
    public static function getManagementTypes($type = "all", $code = "") {

        $managementTypes = [
            ["code" => "unit", "label" => "Unidad"],
            ["code" => "stock", "label" => "Stock"]
        ];

        return Utilities::getValues($managementTypes, $type, $code);

    }

    public static function getStatuses($type = "all", $code = "") {

        $statuses = [
            ["code" => "active", "label" => "Activo"],
            ["code" => "inactive", "label" => "Inactivo"]
        ];

        return Utilities::getValues($statuses, $type, $code);

    }

    // Relationships
    public function company() {

        return $this->belongsTo(Company::class, "company_id", "id");

    }

    public function assetAssignmentsAll() {

        return $this->hasMany(AssetAssignment::class, "asset_id", "id");

    }

    public function assetAssignmentLogsAll() {

        return $this->hasMany(AssetAssignmentLog::class, "asset_id", "id");

    }

    public function branchAssetsAll() {

        return $this->hasMany(BranchAsset::class, "asset_id", "id");

    }

}
