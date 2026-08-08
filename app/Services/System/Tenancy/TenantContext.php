<?php

declare(strict_types=1);

namespace App\Services\System\Tenancy;

use App\Models\System\Tenancy\{TenantDatabase};

final class TenantContext {
    private ?TenantDatabase $tenant = null;

    public function set(?TenantDatabase $tenant): void {

        $this->tenant = $tenant;

    }

    public function get(): ?TenantDatabase {

        return $this->tenant;

    }

    public function active(): bool {

        return $this->tenant !== null;

    }

    public function companyId(): ?int {

        return $this->tenant?->company_id;

    }
}
