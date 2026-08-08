<?php

declare(strict_types=1);

namespace App\Models\System\Sales;

use App\Models\System\Finance\PaymentMethod;
use App\Models\System\Finance\PaymentMethodVariant;
use Illuminate\Database\Eloquent\Model;

final class SalePayment extends Model {
    protected $table = "sale_payments";

    protected $fillable = [
        "company_id",
        "sale_header_id",
        "payment_method_id",
        "payment_method_variant_id",
        "name",
        "amount",
        "reference",
        "note",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
    ];

    protected $casts = [
        "amount" => "App\\Casts\\System\\ConfigurableDecimal",
    ];

    public function paymentMethod() {

        return $this->belongsTo(PaymentMethod::class, "payment_method_id");

    }

    public function paymentMethodVariant() {

        return $this->belongsTo(PaymentMethodVariant::class, "payment_method_variant_id");

    }
}
