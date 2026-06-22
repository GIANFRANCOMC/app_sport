<?php

declare(strict_types=1);

namespace App\Models\System\Finance;

use Illuminate\Database\Eloquent\Model;

final class Tax extends Model {

    protected $table = "taxes";

    protected $fillable = [
        "company_id",
        "code",
        "name",
        "description",
        "rate",
        "calculation_type",
        "operation_type",
        "scope",
        "is_required",
        "is_default",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    protected $casts = [
        "rate" => "decimal:4",
        "is_required" => "boolean",
        "is_default" => "boolean"
    ];

}
