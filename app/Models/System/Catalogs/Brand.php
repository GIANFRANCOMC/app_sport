<?php

declare(strict_types=1);

namespace App\Models\System\Catalogs;

use App\Helpers\System\{Utilities};
use App\Models\System\Organizations\{Company};
use Illuminate\Database\Eloquent\{Model};

class Brand extends Model {
    protected $table = "brands";

    protected $fillable = [
        "company_id",
        "internal_code",
        "name",
        "description",
        "logo_path",
        "origin_country_code",
        "website_url",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
    ];

    protected $appends = [
        "formatted_status",
    ];

    public function getFormattedStatusAttribute(): string {

        return self::getStatuses("first", $this->attributes["status"] ?? "")["label"] ?? "";

    }

    public static function getStatuses($type = "all", $code = "") {

        return Utilities::getValues([
            ["code" => "active", "label" => "Activo"],
            ["code" => "inactive", "label" => "Inactivo"],
        ], $type, $code);

    }

    public function company() {

        return $this->belongsTo(Company::class, "company_id", "id");

    }

    public function products() {

        return $this->hasMany(Item::class, "brand_id", "id")
            ->where("type", "product");

    }
}
