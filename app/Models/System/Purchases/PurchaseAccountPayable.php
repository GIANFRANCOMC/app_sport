<?php

declare(strict_types=1);

namespace App\Models\System\Purchases;

use App\Models\System\General\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PurchaseAccountPayable extends Model {

    protected $table = "purchase_accounts_payable";

    protected $fillable = [
        "company_id",
        "purchase_header_id",
        "supplier_id",
        "currency_id",
        "issue_date",
        "due_date",
        "payment_modality",
        "original_amount",
        "extra_percentage",
        "extra_amount",
        "total_amount",
        "paid_amount",
        "pending_amount",
        "status",
        "observation",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
        "canceled_at",
        "canceled_by"
    ];

    protected $casts = [
        "issue_date" => "date:Y-m-d",
        "due_date" => "date:Y-m-d",
        "original_amount" => "decimal:4",
        "extra_percentage" => "decimal:4",
        "extra_amount" => "decimal:4",
        "total_amount" => "decimal:4",
        "paid_amount" => "decimal:4",
        "pending_amount" => "decimal:4",
        "canceled_at" => "datetime"
    ];

    public function purchase(): BelongsTo {

        return $this->belongsTo(PurchaseHeader::class, "purchase_header_id");

    }

    public function supplier(): BelongsTo {

        return $this->belongsTo(Supplier::class, "supplier_id");

    }

    public function currency(): BelongsTo {

        return $this->belongsTo(Currency::class, "currency_id");

    }

    public function installments(): HasMany {

        return $this->hasMany(PurchasePayableInstallment::class, "purchase_account_payable_id");

    }

    public function payments(): HasMany {

        return $this->hasMany(PurchasePayablePayment::class, "purchase_account_payable_id")
                    ->where("status", "active");

    }

}
