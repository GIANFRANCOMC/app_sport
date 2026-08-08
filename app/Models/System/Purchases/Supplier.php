<?php

declare(strict_types=1);

namespace App\Models\System\Purchases;

use Illuminate\Database\Eloquent\Model;

final class Supplier extends Model {
    protected $table = "suppliers";

    protected $fillable = [
        "company_id",
        "document_type",
        "document_number",
        "name",
        "contact_name",
        "telephone",
        "email",
        "address",
        "payment_term_days",
        "credit_limit",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by",
    ];

    protected $casts = [
        "payment_term_days" => "integer",
        "credit_limit" => "App\\Casts\\System\\ConfigurableDecimal",
    ];

    public function purchases() {

        return $this->hasMany(PurchaseHeader::class, "supplier_id");

    }

    public function contacts() {

        return $this->hasMany(SupplierContact::class, "supplier_id");

    }

    public function bankAccounts() {

        return $this->hasMany(SupplierBankAccount::class, "supplier_id");

    }
}
