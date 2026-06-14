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
        "reason",
        "metadata",
        "created_at"
    ];

    protected $casts = [
        "quantity_before" => "decimal:2",
        "quantity_change" => "decimal:2",
        "quantity_after"  => "decimal:2",
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
