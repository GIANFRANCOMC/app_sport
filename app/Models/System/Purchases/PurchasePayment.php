<?php

declare(strict_types=1);

namespace App\Models\System\Purchases;

use App\Models\System\Finance\PaymentMethod;
use Illuminate\Database\Eloquent\Model;

final class PurchasePayment extends Model {

    protected $table = "purchase_payments";

    protected $fillable = [
        "company_id",
        "purchase_header_id",
        "payment_method_id",
        "name",
        "amount",
        "reference",
        "note",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    protected $casts = [
        "amount" => "decimal:4"
    ];

    public function paymentMethod() {

        return $this->belongsTo(PaymentMethod::class, "payment_method_id");

    }

}
