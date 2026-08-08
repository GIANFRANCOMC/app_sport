<?php

declare(strict_types=1);

namespace App\Models\System\Sales;

use App\Models\System\Catalogs\{Item};
use Illuminate\Database\Eloquent\{Model};

class SaleDeliveryItem extends Model {
    protected $table = "sale_delivery_items";

    protected $appends = [
        "formatted_status",
    ];

    protected $fillable = [
        "company_id",
        "sale_delivery_id",
        "sale_body_id",
        "item_id",
        "quantity_ordered",
        "quantity_delivered",
        "quantity_pending",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
    ];

    protected $casts = [
        "quantity_ordered" => "App\\Casts\\System\\ConfigurableDecimal",
        "quantity_delivered" => "App\\Casts\\System\\ConfigurableDecimal",
        "quantity_pending" => "App\\Casts\\System\\ConfigurableDecimal",
    ];

    public function getFormattedStatusAttribute(): string {

        return SaleDelivery::getStatuses("first", $this->attributes["status"] ?? "")["label"] ?? "";

    }

    public function delivery() {

        return $this->belongsTo(SaleDelivery::class, "sale_delivery_id", "id");

    }

    public function saleBody() {

        return $this->belongsTo(SaleBody::class, "sale_body_id", "id");

    }

    public function item() {

        return $this->belongsTo(Item::class, "item_id", "id");

    }
}
