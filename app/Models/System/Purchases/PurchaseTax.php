<?php

declare(strict_types=1);

namespace App\Models\System\Purchases;

use App\Models\System\Finance\Tax;
use Illuminate\Database\Eloquent\Model;

final class PurchaseTax extends Model {

    protected $table = "purchase_taxes";

    protected $fillable = [
        "company_id",
        "purchase_header_id",
        "tax_id",
        "name",
        "description",
        "rate",
        "calculation_type",
        "operation_type",
        "is_required",
        "quantity",
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
        "is_required" => "boolean",
        "quantity" => "integer",
        "base_amount" => "decimal:4",
        "amount" => "decimal:4"
    ];

    public function tax() {

        return $this->belongsTo(Tax::class, "tax_id");

    }

}
