<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\System\Tenancy\{TenantDatabase, TenantDomain};
use App\Services\System\Tenancy\{LandlordSchemaService, TenantConnectionManager};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{Artisan, Cache, DB};
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

final class CreateTenantCompany extends Command {

    protected $signature = 'tenant:create
        {slug : Subdominio/código único del cliente}
        {--domain= : Subdominio completo. Debe coincidir con slug + TENANCY_BASE_DOMAIN}
        {--database= : Nombre de la base de datos tenant. Si se omite usa TENANT_DB_PREFIX + slug}
        {--company-id=1 : ID raíz de company dentro de la BD tenant}
        {--commercial-name= : Nombre comercial inicial}
        {--legal-name= : Razón social inicial}
        {--document-number=99999999999 : Documento inicial}
        {--force : Actualiza el registro landlord si ya existe}
        {--skip-create-database : Usa una base creada previamente por infraestructura}
        {--skip-migrate : Solo crea DB y registry, sin ejecutar migraciones tenant}
        {--skip-cache-clear : No ejecuta optimize:clear al final}';

    protected $description = 'Crea una compañía tenant con base de datos aislada y subdominio registrado.';

    public function handle(TenantConnectionManager $connectionManager): int {

        $slug = $this->normalizeSlug((string) $this->argument('slug'));
        $companyId = (int) $this->option('company-id');
        $databaseName = $this->normalizeDatabaseName((string) ($this->option('database') ?: config('tenancy.database_prefix', 'gympe_tenant_') . $slug));
        $domain = $this->normalizeDomain((string) ($this->option('domain') ?: $this->defaultDomain($slug)));

        $this->assertSubdomainIsAllowed($slug, $domain);

        if($companyId <= 0) {
            throw new InvalidArgumentException('company-id debe ser mayor a 0.');
        }

        LandlordSchemaService::ensure();
        $this->assertRegistryIsAvailable($slug, $domain);

        $tenant = null;

        try {
            if(!$this->option('skip-create-database')) {
                $this->createDatabase($databaseName);
            }

            $tenant = $this->upsertTenantRegistry($slug, $companyId, $databaseName, $domain);
            $connectionManager->connect($tenant);

            if(!$this->option('skip-migrate')) {
                $this->runTenantMigrations();
                $this->hydrateCompany($companyId, $slug);
                $this->call('company:enable', ['company_id' => $companyId]);
            }

            DB::connection('landlord')
                ->table('tenant_databases')
                ->where('id', $tenant->id)
                ->update(['status' => 'active', 'updated_at' => now()]);

            Cache::forget('tenancy:resolver:' . hash('sha256', $domain));

            if(!$this->option('skip-cache-clear')) {
                Artisan::call('optimize:clear');
            }

            $this->info("Tenant {$slug} creado correctamente.");
            $this->line("Dominio: {$domain}");
            $this->line("Base de datos: {$databaseName}");

            return self::SUCCESS;
        } catch(Throwable $exception) {
            if($tenant) {
                DB::connection('landlord')
                    ->table('tenant_databases')
                    ->where('id', $tenant->id)
                    ->update(['status' => 'provisioning', 'updated_at' => now()]);
            }

            $this->error($exception->getMessage());
            return self::FAILURE;
        }

    }

    private function normalizeSlug(string $slug): string {

        $slug = Str::slug($slug);
        if($slug === '') {
            throw new InvalidArgumentException('El slug no puede estar vacío.');
        }

        if(strlen($slug) > 63 || !preg_match('/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/', $slug)) {
            throw new InvalidArgumentException('El subdominio no tiene un formato válido.');
        }

        if(in_array($slug, config('tenancy.reserved_subdomains', []), true)) {
            throw new InvalidArgumentException('El subdominio está reservado por la plataforma.');
        }

        return $slug;

    }

    private function normalizeDomain(string $domain): string {

        $domain = strtolower(trim($domain));
        $domain = Str::before($domain, ':');

        if($domain === '' || !preg_match('/^[a-z0-9.-]+$/', $domain)) {
            throw new InvalidArgumentException('El dominio no es válido.');
        }

        return $domain;

    }

    private function normalizeDatabaseName(string $database): string {

        $database = strtolower(trim($database));
        $database = preg_replace('/[^a-z0-9_]/', '_', $database) ?: '';

        if($database === '') {
            throw new InvalidArgumentException('El nombre de base de datos no es válido.');
        }

        $prefix = (string) config('tenancy.database_prefix', 'gympe_tenant_');
        if(config('tenancy.enforce_database_prefix', true) && !str_starts_with($database, $prefix)) {
            throw new InvalidArgumentException("La base de datos debe comenzar con {$prefix}.");
        }

        return $database;

    }

    private function defaultDomain(string $slug): string {

        $baseDomain = trim((string) config('tenancy.base_domain'));
        return $baseDomain !== '' ? "{$slug}.{$baseDomain}" : $slug;

    }

    private function assertSubdomainIsAllowed(string $slug, string $domain): void {

        $expectedDomain = $this->defaultDomain($slug);

        if($domain !== $expectedDomain) {
            throw new InvalidArgumentException(
                "Solo se permiten subdominios del dominio base. Use {$expectedDomain}."
            );
        }

    }

    private function assertRegistryIsAvailable(string $slug, string $domain): void {

        $existingTenant = DB::connection('landlord')
            ->table('tenant_databases')
            ->where('slug', $slug)
            ->first();

        if($existingTenant && !$this->option('force')) {
            throw new InvalidArgumentException('Ya existe un tenant con ese subdominio/slug.');
        }

        $existingDomain = DB::connection('landlord')
            ->table('tenant_domains')
            ->where('domain', $domain)
            ->first();

        if($existingDomain && (!$existingTenant || (int) $existingDomain->tenant_database_id !== (int) $existingTenant->id)) {
            throw new InvalidArgumentException('Ya existe un tenant con ese dominio.');
        }

    }

    private function createDatabase(string $databaseName): void {

        $quoted = '`' . str_replace('`', '``', $databaseName) . '`';
        DB::connection('landlord')->statement("CREATE DATABASE IF NOT EXISTS {$quoted} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    }

    private function upsertTenantRegistry(string $slug, int $companyId, string $databaseName, string $domain) {

        $tenantPayload = [
            'company_id' => $companyId,
            'database_name' => $databaseName,
            'status' => 'provisioning',
            'updated_at' => now()
        ];

        $tenant = TenantDatabase::query()->updateOrCreate(
            ['slug' => $slug],
            $tenantPayload
        );

        TenantDomain::query()->updateOrCreate(
            ['domain' => $domain],
            [
                'tenant_database_id' => $tenant->id,
                'type' => 'subdomain',
                'is_primary' => true,
                'status' => 'active',
            ]
        );

        return $tenant->refresh();

    }

    private function runTenantMigrations(): void {

        Artisan::call('migrate', [
            '--database' => config('tenancy.tenant_connection', 'tenant'),
            '--path' => 'database/migrations',
            '--force' => true
        ]);

        $this->line(Artisan::output());

    }

    private function hydrateCompany(int $companyId, string $slug): void {

        DB::table('companies')
            ->where('id', $companyId)
            ->update([
                'slug' => $slug,
                'commercial_name' => $this->option('commercial-name') ?: Str::headline($slug),
                'legal_name' => $this->option('legal-name') ?: Str::upper(Str::headline($slug)),
                'document_number' => (string) $this->option('document-number'),
                'updated_at' => now()
            ]);

    }

}
