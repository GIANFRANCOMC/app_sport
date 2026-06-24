<?php

declare(strict_types=1);

namespace App\Models\System\Finance;

use Illuminate\Database\Eloquent\Model;

final class PaymentMethod extends Model {

    protected $table = "payment_methods";

    protected $fillable = [
        "company_id",
        "code",
        "name",
        "sunat_code",
        "image_path",
        "scope",
        "requires_reference",
        "is_default",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    protected $casts = [
        "requires_reference" => "boolean",
        "is_default" => "boolean"
    ];

}
