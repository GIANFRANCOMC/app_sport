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
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    public function purchases() {

        return $this->hasMany(PurchaseHeader::class, "supplier_id");

    }

}
