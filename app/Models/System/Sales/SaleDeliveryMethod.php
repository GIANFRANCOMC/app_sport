<?php

declare(strict_types=1);

namespace App\Models\System\Sales;

use App\Models\System\Organizations\Company;
use Illuminate\Database\Eloquent\Model;

final class SaleDeliveryMethod extends Model {

    protected $table = "sale_delivery_methods";

    protected $fillable = [
        "company_id",
        "code",
        "name",
        "description",
        "sort_order",
        "is_default",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    protected $casts = [
        "sort_order" => "integer",
        "is_default" => "boolean"
    ];

    public function company() {

        return $this->belongsTo(Company::class, "company_id", "id");

    }

}
