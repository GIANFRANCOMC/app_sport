<?php

declare(strict_types=1);

namespace App\Services\System\Tenancy;

use App\Models\System\Tenancy\{TenantAuditLog, TenantDatabase};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Cache, DB, Schema};
use RuntimeException;
use Throwable;

final class TenantAdministrationService {

    public function __construct(private readonly TenantConnectionManager $connectionManager) {}

    public function list(?string $status = null): Collection {

        return TenantDatabase::query()
            ->with('domains')
            ->when($status, fn($query) => $query->where('status', $status))
            ->orderBy('slug')
            ->get();

    }

    public function health(TenantDatabase $tenant): array {

        $startedAt = microtime(true);

        try {
            $this->connectionManager->connect($tenant);
            DB::connection((string) config('tenancy.tenant_connection', 'tenant'))->selectOne('SELECT 1');
            $hasCompanies = Schema::connection((string) config('tenancy.tenant_connection', 'tenant'))
                ->hasTable('companies');

            $result = [
                'healthy' => $hasCompanies,
                'database' => $tenant->database_name,
                'latency_ms' => round((microtime(true) - $startedAt) * 1000, 2),
                'message' => $hasCompanies
                    ? 'Conexión disponible y esquema base presente.'
                    : 'La conexión responde, pero falta la tabla companies.'
            ];

            $this->audit($tenant, 'health_check', $hasCompanies ? 'success' : 'failure', $result);

            return $result;
        }catch(Throwable $exception) {
            $result = [
                'healthy' => false,
                'database' => $tenant->database_name,
                'latency_ms' => round((microtime(true) - $startedAt) * 1000, 2),
                'message' => $exception->getMessage()
            ];
            $this->audit($tenant, 'health_check', 'failure', $result);

            return $result;
        }finally {
            $this->connectionManager->disconnect();
        }

    }

    public function changeStatus(string $slug, string $status, ?string $actor = null): TenantDatabase {

        if(!in_array($status, ['active', 'inactive', 'suspended'], true)) {
            throw new RuntimeException('El estado solicitado no es válido para un tenant.');
        }

        $tenant = TenantDatabase::query()->where('slug', $slug)->first();
        if(!$tenant) {
            throw new RuntimeException('El tenant solicitado no existe.');
        }

        $previousStatus = $tenant->status;
        $tenant->forceFill(['status' => $status, 'updated_at' => now()])->save();
        $this->clearResolverCache($tenant);
        $this->audit($tenant, 'status_changed', 'success', [
            'previous_status' => $previousStatus,
            'new_status' => $status
        ], $actor);

        return $tenant->fresh('domains');

    }

    public function clearResolverCache(?TenantDatabase $tenant = null, ?string $actor = null): int {

        $tenants = $tenant ? collect([$tenant->loadMissing('domains')]) : $this->list();
        $cleared = 0;

        foreach($tenants as $currentTenant) {
            foreach($currentTenant->domains as $domain) {
                if(Cache::forget(TenantResolver::cacheKey((string) $domain->domain))) {
                    $cleared++;
                }
            }
            $this->audit($currentTenant, 'resolver_cache_cleared', 'success', [
                'domains' => $currentTenant->domains->pluck('domain')->values()->all()
            ], $actor);
        }

        return $cleared;

    }

    public function audit(
        ?TenantDatabase $tenant,
        string $action,
        string $result,
        array $context = [],
        ?string $actor = null,
        ?string $host = null,
        ?string $ipAddress = null
    ): void {

        try {
            if(!Schema::connection((string) config('tenancy.landlord_connection', 'landlord'))->hasTable('tenant_audit_logs')) {
                return;
            }

            TenantAuditLog::query()->create([
                'tenant_database_id' => $tenant?->id,
                'company_id' => $tenant?->company_id,
                'action' => $action,
                'result' => $result,
                'host' => $host,
                'ip_address' => $ipAddress,
                'actor' => $actor,
                'context' => $context === [] ? null : $context,
                'occurred_at' => now()
            ]);
        }catch(Throwable) {
            // La auditoría no debe convertir un rechazo seguro o un comando operativo en un error 500.
        }

    }

}
