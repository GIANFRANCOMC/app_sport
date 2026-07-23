<?php

declare(strict_types=1);

namespace App\Models\System\Sales;

use App\Helpers\System\Utilities;
use App\Models\System\Organizations\{Company, User};
use App\Models\System\Warehouses\Warehouse;
use Illuminate\Database\Eloquent\Model;

class SaleDelivery extends Model {

    protected $table = "sale_deliveries";

    protected $appends = [
        "formatted_status"
    ];

    protected $fillable = [
        "company_id",
        "sale_header_id",
        "warehouse_id",
        "total_quantity",
        "delivered_quantity",
        "pending_quantity",
        "status",
        "last_delivered_at",
        "last_delivered_by",
        "observation",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
        "canceled_at",
        "canceled_by"
    ];

    protected $casts = [
        "total_quantity"     => "App\\Casts\\System\\ConfigurableDecimal",
        "delivered_quantity" => "App\\Casts\\System\\ConfigurableDecimal",
        "pending_quantity"   => "App\\Casts\\System\\ConfigurableDecimal",
        "last_delivered_at"  => "datetime"
    ];

    public function getFormattedStatusAttribute(): string {

        return self::getStatuses("first", $this->attributes["status"] ?? "")["label"] ?? "";

    }

    public static function getStatuses($type = "all", $code = "") {

        $statuses = [
            ["code" => "pending", "label" => "Pendiente"],
            ["code" => "partial", "label" => "Parcial"],
            ["code" => "delivered", "label" => "Entregado"],
            ["code" => "canceled", "label" => "Anulado"]
        ];

        return Utilities::getValues($statuses, $type, $code);

    }

    public function company() {

        return $this->belongsTo(Company::class, "company_id", "id");

    }

    public function saleHeader() {

        return $this->belongsTo(SaleHeader::class, "sale_header_id", "id");

    }

    public function warehouse() {

        return $this->belongsTo(Warehouse::class, "warehouse_id", "id");

    }

    public function lastDeliveredBy() {

        return $this->belongsTo(User::class, "last_delivered_by", "id");

    }

    public function items() {

        return $this->hasMany(SaleDeliveryItem::class, "sale_delivery_id", "id");

    }

    public function events() {

        return $this->hasMany(SaleDeliveryEvent::class, "sale_delivery_id", "id");

    }

}
