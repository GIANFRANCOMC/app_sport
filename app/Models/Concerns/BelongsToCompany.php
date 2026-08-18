<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\System\Organizations\{Company};
use Illuminate\Database\Eloquent\{Builder, Relations\BelongsTo};

trait BelongsToCompany {
    public function scopeForCompany(Builder $query, int $companyId): Builder {

        return $query->where($query->qualifyColumn("company_id"), $companyId);

    }

    public function company(): BelongsTo {

        return $this->belongsTo(Company::class, "company_id");

    }
}
