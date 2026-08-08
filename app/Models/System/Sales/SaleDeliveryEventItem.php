<?php

declare(strict_types=1);

namespace App\Models\System\Sales;

use App\Models\System\Catalogs\Item;
use App\Models\System\Warehouses\InventoryMovement;
use Illuminate\Database\Eloquent\Model;

class SaleDeliveryEventItem extends Model {
    protected $table = "sale_delivery_event_items";

    public $timestamps = false;

    protected $fillable = [
        "company_id",
        "sale_delivery_event_id",
        "sale_delivery_item_id",
        "sale_body_id",
        "item_id",
        "inventory_movement_id",
        "quantity",
        "created_at",
    ];

    protected $casts = [
        "quantity" => "App\\Casts\\System\\ConfigurableDecimal",
        "created_at" => "datetime",
    ];

    public function event() {

        return $this->belongsTo(SaleDeliveryEvent::class, "sale_delivery_event_id", "id");

    }

    public function deliveryItem() {

        return $this->belongsTo(SaleDeliveryItem::class, "sale_delivery_item_id", "id");

    }

    public function saleBody() {

        return $this->belongsTo(SaleBody::class, "sale_body_id", "id");

    }

    public function item() {

        return $this->belongsTo(Item::class, "item_id", "id");

    }

    public function inventoryMovement() {

        return $this->belongsTo(InventoryMovement::class, "inventory_movement_id", "id");

    }
}
