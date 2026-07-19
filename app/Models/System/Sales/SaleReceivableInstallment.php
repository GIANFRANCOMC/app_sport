<?php

declare(strict_types=1);

namespace App\Models\System\Sales;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SaleReceivableInstallment extends Model {

    protected $table = "sale_receivable_installments";

    protected $fillable = [
        "company_id",
        "sale_account_receivable_id",
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

    public function accountReceivable(): BelongsTo {

        return $this->belongsTo(SaleAccountReceivable::class, "sale_account_receivable_id");

    }

}
