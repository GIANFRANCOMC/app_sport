<?php

declare(strict_types=1);

namespace App\Models\System\Purchases;

use App\Models\System\Catalogs\{Item};
use Illuminate\Database\Eloquent\{Model};

final class PurchaseItem extends Model {
    protected $table = "purchase_items";

    protected $fillable = [
        "company_id",
        "purchase_header_id",
        "item_id",
        "name",
        "quantity",
        "received_quantity",
        "unit_cost",
        "allocated_expense_total",
        "inventory_unit_cost",
        "subtotal",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
    ];

    protected $casts = [
        "quantity" => "App\\Casts\\System\\ConfigurableDecimal",
        "received_quantity" => "App\\Casts\\System\\ConfigurableDecimal",
        "unit_cost" => "App\\Casts\\System\\ConfigurableDecimal",
        "allocated_expense_total" => "App\\Casts\\System\\ConfigurableDecimal",
        "inventory_unit_cost" => "App\\Casts\\System\\ConfigurableDecimal",
        "subtotal" => "App\\Casts\\System\\ConfigurableDecimal",
    ];

    protected $appends = ["remaining_quantity"];

    public function getRemainingQuantityAttribute(): float {

        return round(
            (float) ($this->attributes["quantity"] ?? 0)
            - (float) ($this->attributes["received_quantity"] ?? 0),
            2
        );

    }

    public function purchase() {

        return $this->belongsTo(PurchaseHeader::class, "purchase_header_id");

    }

    public function item() {

        return $this->belongsTo(Item::class, "item_id");

    }
}
