<?php

namespace App\Models\System\Warehouses;

use App\Helpers\System\{Utilities};
use App\Models\System\Organizations\{Branch};
use Illuminate\Database\Eloquent\{Model};

class Warehouse extends Model {
    protected $table = "warehouses";

    protected $primaryKey = "id";

    public $incrementing = true;

    public $timestamps = true;

    public static $snakeAttributes = true;

    protected $appends = [
        "formatted_status",
    ];

    protected $fillable = [
        "company_id",
        "branch_id",
        "name",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
    ];

    public static function plainName(?string $name): string {

        $name = trim((string) $name);

        if($name === "") {

            return "";

        }

        $normalized = preg_replace("/^.+\\s-\\s((?:Almacén|Almacen)\\s+\\d+)\$/u", "\$1", $name);

        return trim($normalized ?: $name);

    }

    public function getNameAttribute($value): string {

        return self::plainName($value);

    }

    public function setNameAttribute($value): void {

        $this->attributes["name"] = self::plainName($value);

    }

    // Appends
    public function getFormattedStatusAttribute() {

        $status = $this->attributes["status"] ?? null;

        return $status ? (self::getStatuses("first", $status)["label"] ?? "") : "";

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
    public function branch() {

        return $this->belongsTo(Branch::class, "branch_id", "id");

    }

    public function inventoryMovements() {

        return $this->hasMany(InventoryMovement::class, "warehouse_id", "id");

    }
}
