<?php

namespace App\Models\System\Catalogs;

use App\Helpers\System\{Utilities};
use App\Models\System\Catalogs\{Category};
use Illuminate\Database\Eloquent\{Model};

class CategoryItem extends Model {
    protected $table = "category_items";

    protected $primaryKey = "id";

    public $incrementing = true;

    public $timestamps = true;

    public static $snakeAttributes = true;

    protected $appends = [
        "formatted_status",
    ];

    protected $fillable = [
        "company_id",
        "category_id",
        "item_id",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
    ];

    // Appends
    public function getFormattedStatusAttribute() {

        return self::getStatuses("first", $this->attributes["status"] ?? "")["label"] ?? "";

    }

    // Functions
    public static function getStatuses($type = "all", $code = "") {

        $statuses = [
            ["code" => "active", "label" => "Activo"],
            ["code" => "inactive", "label" => "Inactivo"],
        ];

        return Utilities::getValues($statuses, $type, $code);

    }

    // Relationships
    public function category() {

        return $this->belongsTo(Category::class, "category_id", "id");

    }

    public function item() {

        return $this->belongsTo(Item::class, "item_id", "id");

    }
}
