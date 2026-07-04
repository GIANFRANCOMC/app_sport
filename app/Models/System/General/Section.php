<?php

namespace App\Models\System\General;

use App\Helpers\System\Utilities;
use Illuminate\Database\Eloquent\Model;

class Section extends Model {

    protected $table               = "sections";
    protected $primaryKey          = "id";
    public $incrementing           = true;
    public $timestamps             = true;
    public static $snakeAttributes = true;

    protected $appends = [
        "formatted_status"
    ];

    protected $fillable = [
        "slug",
        "name",
        "order",
        "dom_id",
        "dom_label",
        "dom_icon",
        "has_sub_menu",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    // Appends
    public function getFormattedStatusAttribute() {

        return self::getStatuses("first", $this->attributes["status"] ?? "")["label"] ?? "";

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
    public function subSections() {

        return $this->hasMany(SubSection::class, "section_id", "id")
                    ->whereIn("status", ["active"]);

    }

}
