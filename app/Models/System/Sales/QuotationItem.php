<?php

declare(strict_types=1);

namespace App\Models\System\Sales;

use Illuminate\Database\Eloquent\Model;

use App\Models\System\Catalogs\Item;
use App\Models\System\General\Currency;

final class QuotationItem extends Model {

    protected $table = "quotation_items";

    protected $fillable = [
        "company_id",
        "quotation_header_id",
        "item_id",
        "currency_id",
        "name",
        "type",
        "quantity",
        "price",
        "price_includes_tax",
        "total",
        "observation",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    protected $casts = [
        "quantity" => "decimal:4",
        "price" => "decimal:4",
        "price_includes_tax" => "boolean",
        "total" => "decimal:4"
    ];

    public function item() {
        return $this->belongsTo(Item::class, "item_id");
    }

    public function currency() {
        return $this->belongsTo(Currency::class, "currency_id");
    }

}
