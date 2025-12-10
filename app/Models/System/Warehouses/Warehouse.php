<?php

namespace App\Models\System\Warehouses;

use App\Helpers\System\Utilities;
use Illuminate\Database\Eloquent\Model;

use App\Models\System\Organizations\{Branch};

class Warehouse extends Model {

    protected $table               = "warehouses";
    protected $primaryKey          = "id";
    public $incrementing           = true;
    public $timestamps             = true;
    public static $snakeAttributes = true;

    protected $appends = [
        "formatted_status"
    ];

    protected $fillable = [
        "branch_id",
        "name",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    // Appends
    public function getFormattedStatusAttribute() {

        return self::getStatuses("first", $this->attributes["status"])["label"] ?? "";

    }

    // Functions
    public static function getStatuses($type = "all", $code = "") {

        $statuses = [
            ["code" => "active", "label" => "Activo"],
            ["code" => "inactive", "label" => "Inactivo"]
        ];

        return Utilities::getValues($statuses, $type, $code);

    }

    public static function getAll(string $type = "default", ?int $company_id = null) {

        return Warehouse::with("branch")
                        ->whereHas("branch", function($query) use($company_id) {

                            $query->where("company_id", $company_id);

                        })
                        ->when(in_array($type, ["stock_management"]), function($query) {

                            $query->whereIn("status", ["active"]);

                        })
                        ->get();

    }

    // Relationships
    public function branch() {

        return $this->belongsTo(Branch::class, "branch_id", "id");

    }

}
