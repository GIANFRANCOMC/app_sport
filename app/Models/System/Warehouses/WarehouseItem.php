<?php

namespace App\Models\System\Warehouses;

use App\Helpers\System\Utilities;
use App\Models\System\Catalogs\{Item};
use Illuminate\Database\Eloquent\Model;

class WarehouseItem extends Model {
    protected $table = "warehouse_items";

    protected $primaryKey = "id";

    public $incrementing = true;

    public $timestamps = true;

    public static $snakeAttributes = true;

    protected $appends = [
        "formatted_status",
    ];

    protected $fillable = [
        "company_id",
        "warehouse_id",
        "item_id",
        "quantity",
        "minimum_stock",
        "average_cost",
        "inventory_value",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
    ];

    protected $casts = [
        "quantity" => "App\\Casts\\System\\ConfigurableDecimal",
        "minimum_stock" => "App\\Casts\\System\\ConfigurableDecimal",
        "average_cost" => "App\\Casts\\System\\ConfigurableDecimal",
        "inventory_value" => "App\\Casts\\System\\ConfigurableDecimal",
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
    public function warehouse() {

        return $this->belongsTo(Warehouse::class, "warehouse_id", "id");

    }

    public function item() {

        return $this->belongsTo(Item::class, "item_id", "id");

    }

    public function stockAlerts() {

        return $this->hasMany(InventoryStockAlert::class, "warehouse_item_id");

    }
}
