<?php

declare(strict_types=1);

namespace App\Models\System\Purchases;

use Illuminate\Database\Eloquent\Model;

use App\Models\System\Catalogs\Item;
use App\Models\System\Warehouses\InventoryMovement;

final class PurchaseReceiptItem extends Model {

    protected $table = "purchase_receipt_items";
    public $timestamps = false;

    protected $fillable = [
        "purchase_receipt_id",
        "purchase_item_id",
        "item_id",
        "inventory_movement_id",
        "quantity",
        "unit_cost",
        "total_cost",
        "created_at",
        "created_by"
    ];

    protected $casts = [
        "quantity" => "decimal:2",
        "unit_cost" => "decimal:4",
        "total_cost" => "decimal:2",
        "created_at" => "datetime"
    ];

    public function receipt() {

        return $this->belongsTo(PurchaseReceipt::class, "purchase_receipt_id");

    }

    public function purchaseItem() {

        return $this->belongsTo(PurchaseItem::class, "purchase_item_id");

    }

    public function item() {

        return $this->belongsTo(Item::class, "item_id");

    }

    public function inventoryMovement() {

        return $this->belongsTo(InventoryMovement::class, "inventory_movement_id");

    }

}
