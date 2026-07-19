<?php

declare(strict_types=1);

namespace App\Models\System\Purchases;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PurchasePayableInstallment extends Model {

    protected $table = "purchase_payable_installments";

    protected $fillable = [
        "company_id",
        "purchase_account_payable_id",
        "installment_number",
        "due_date",
        "amount",
        "paid_amount",
        "pending_amount",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    protected $casts = [
        "due_date" => "date:Y-m-d",
        "amount" => "decimal:4",
        "paid_amount" => "decimal:4",
        "pending_amount" => "decimal:4"
    ];

    public function accountPayable(): BelongsTo {

        return $this->belongsTo(PurchaseAccountPayable::class, "purchase_account_payable_id");

    }

}
