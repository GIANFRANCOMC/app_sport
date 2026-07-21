<?php

declare(strict_types=1);

namespace App\Models\System\Warehouses;

use Illuminate\Database\Eloquent\Model;

use App\Models\System\Catalogs\Item;
use App\Models\System\Organizations\{Company, User};

class InventoryMovement extends Model {

    protected $table = "inventory_movements";
    public $timestamps = false;
    protected $appends = ["reference"];

    protected $fillable = [
        "company_id",
        "warehouse_id",
        "item_id",
        "user_id",
        "movement_type",
        "origin_type",
        "origin_id",
        "quantity_before",
        "quantity_change",
        "quantity_after",
        "unit_cost",
        "value_before",
        "value_change",
        "value_after",
        "reason",
        "metadata",
        "created_at"
    ];

    protected $casts = [
        "quantity_before" => "App\\Casts\\System\\ConfigurableDecimal",
        "quantity_change" => "App\\Casts\\System\\ConfigurableDecimal",
        "quantity_after"  => "App\\Casts\\System\\ConfigurableDecimal",
        "unit_cost"       => "App\\Casts\\System\\ConfigurableDecimal",
        "value_before"    => "App\\Casts\\System\\ConfigurableDecimal",
        "value_change"    => "App\\Casts\\System\\ConfigurableDecimal",
        "value_after"     => "App\\Casts\\System\\ConfigurableDecimal",
        "metadata"        => "array",
        "created_at"      => "datetime"
    ];

    public function getReferenceAttribute(): ?string {

        return ($this->metadata ?? [])["reference"] ?? null;

    }

    public function company() {

        return $this->belongsTo(Company::class, "company_id", "id");

    }

    public function warehouse() {

        return $this->belongsTo(Warehouse::class, "warehouse_id", "id");

    }

    public function item() {

        return $this->belongsTo(Item::class, "item_id", "id");

    }

    public function user() {

        return $this->belongsTo(User::class, "user_id", "id");

    }

}
