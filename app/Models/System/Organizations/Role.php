<?php

namespace App\Models\System\Organizations;

use App\Helpers\System\Utilities;
use Illuminate\Database\Eloquent\Model;

use App\Models\System\Organizations\{Company};

class Role extends Model {

    protected $table               = "roles";
    protected $primaryKey          = "id";
    public $incrementing           = true;
    public $timestamps             = true;
    public static $snakeAttributes = true;

    protected $appends = [
        "formatted_status"
    ];

    protected $fillable = [
        "company_id",
        "slug",
        "name",
        "is_full_access",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    // Appends
    public function getFormattedStatusAttribute() {

        $status = $this->attributes["status"] ?? null;

        return $status ? (self::getStatuses("first", $status)["label"] ?? "") : "";

    }

    // Functions
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

    public function users() {

        return $this->hasMany(User::class, "role_id", "id")
                    ->whereIn("status", ["active"]);

    }

    public function roleSubSections() {

        return $this->hasMany(RoleSubSection::class, "role_id", "id")
                    ->whereIn("status", ["active"]);

    }

    public function subSections() {

        return $this->belongsToMany(
            \App\Models\System\General\SubSection::class,
            "role_sub_sections",
            "role_id",
            "sub_section_id"
        )->wherePivot("status", "active");

    }

}
