<?php

declare(strict_types=1);

namespace App\Models\System\Purchases;

use App\Models\System\Finance\Tax;
use Illuminate\Database\Eloquent\Model;

final class PurchaseTax extends Model {

    protected $table = "purchase_taxes";

    protected $fillable = [
        "purchase_header_id",
        "tax_id",
        "name",
        "rate",
        "base_amount",
        "amount",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    protected $casts = [
        "rate" => "decimal:4",
        "base_amount" => "decimal:2",
        "amount" => "decimal:2"
    ];

    public function tax() {

        return $this->belongsTo(Tax::class, "tax_id");

    }

}
