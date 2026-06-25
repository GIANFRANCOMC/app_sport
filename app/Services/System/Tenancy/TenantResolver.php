<?php

declare(strict_types=1);

namespace App\Services\System\Tenancy;

use App\Models\System\Tenancy\{TenantDatabase, TenantDomain};
use Illuminate\Support\Str;

final class TenantResolver {

    public function resolveByHost(string $host): ?TenantDatabase {

        $host = $this->normalizeHost($host);
        if($host === '' || $this->isCentralDomain($host)) {
            return null;
        }

        LandlordSchemaService::ensure();

        $tenant = $this->resolveByDomain($host) ?? $this->resolveBySubdomain($host);

        if($tenant) {
            $tenant->forceFill(['last_resolved_at' => now()])->save();
        }

        return $tenant;

    }

    public function normalizeHost(string $host): string {

        $host = strtolower(trim($host));
        return Str::before($host, ':');

    }

    private function isCentralDomain(string $host): bool {

        return in_array($host, config('tenancy.central_domains', []), true);

    }

    private function resolveByDomain(string $host): ?TenantDatabase {

        $domain = TenantDomain::query()
            ->where('domain', $host)
            ->where('status', 'active')
            ->with('tenantDatabase')
            ->first();

        if(!$domain || !$domain->tenantDatabase || $domain->tenantDatabase->status !== 'active') {
            return null;
        }

        return $domain->tenantDatabase;

    }

    private function resolveBySubdomain(string $host): ?TenantDatabase {

        $baseDomain = strtolower((string) config('tenancy.base_domain'));
        if($baseDomain === '' || !Str::endsWith($host, ".{$baseDomain}")) {
            return null;
        }

        $slug = Str::beforeLast($host, ".{$baseDomain}");
        if($slug === '' || Str::contains($slug, '.')) {
            return null;
        }

        return TenantDatabase::query()
            ->where('slug', $slug)
            ->where('status', 'active')
            ->first();

    }

}