<?php

declare(strict_types=1);

namespace App\Models\System\Warehouses;

use App\Models\System\Organizations\{Company, User};
use Illuminate\Database\Eloquent\Model;

final class InventoryStockAlert extends Model {

    protected $table = "inventory_stock_alerts";

    protected $fillable = [
        "company_id",
        "warehouse_item_id",
        "quantity",
        "minimum_stock",
        "status",
        "detected_at",
        "resolved_at",
        "resolved_by"
    ];

    protected $casts = [
        "quantity" => "decimal:4",
        "minimum_stock" => "decimal:4",
        "detected_at" => "datetime",
        "resolved_at" => "datetime"
    ];

    public function company() {

        return $this->belongsTo(Company::class);

    }

    public function warehouseItem() {

        return $this->belongsTo(WarehouseItem::class);

    }

    public function resolvedBy() {

        return $this->belongsTo(User::class, "resolved_by");

    }

}
