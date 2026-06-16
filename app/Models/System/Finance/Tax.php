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
        "rate",
        "scope",
        "is_default",
        "status",
        "created_at",
        "created_by",
        "updated_at",
        "updated_by"
    ];

    protected $casts = [
        "rate" => "decimal:4",
        "is_default" => "boolean"
    ];

}
