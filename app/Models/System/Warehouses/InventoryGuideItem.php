<?php

declare(strict_types=1);

namespace App\Models\System\Warehouses;

use App\Models\System\Catalogs\Item;
use Illuminate\Database\Eloquent\Model;

final class InventoryGuideItem extends Model {
    protected $fillable = [
        "company_id",
        "inventory_guide_id",
        "item_id",
        "inventory_movement_id",
        "quantity",
        "unit_cost",
    ];

    protected $casts = ["quantity" => "App\\Casts\\System\\ConfigurableDecimal", "unit_cost" => "App\\Casts\\System\\ConfigurableDecimal"];

    public function guide() {
        return $this->belongsTo(InventoryGuide::class, "inventory_guide_id");
    }

    public function item() {
        return $this->belongsTo(Item::class);
    }

    public function movement() {
        return $this->belongsTo(InventoryMovement::class, "inventory_movement_id");
    }
}
