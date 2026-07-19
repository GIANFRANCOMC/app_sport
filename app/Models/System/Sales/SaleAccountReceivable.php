<?php

declare(strict_types=1);

namespace App\Models\System\Sales;

use App\Models\System\Customers\Customer;
use App\Models\System\General\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SaleAccountReceivable extends Model {

    protected $table = "sale_accounts_receivable";

    protected $fillable = [
        "company_id",
        "sale_header_id",
        "customer_id",
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

    public function sale(): BelongsTo {

        return $this->belongsTo(SaleHeader::class, "sale_header_id");

    }

    public function customer(): BelongsTo {

        return $this->belongsTo(Customer::class, "customer_id");

    }

    public function currency(): BelongsTo {

        return $this->belongsTo(Currency::class, "currency_id");

    }

    public function installments(): HasMany {

        return $this->hasMany(SaleReceivableInstallment::class, "sale_account_receivable_id");

    }

    public function payments(): HasMany {

        return $this->hasMany(SaleReceivablePayment::class, "sale_account_receivable_id")
                    ->where("status", "active");

    }

}
