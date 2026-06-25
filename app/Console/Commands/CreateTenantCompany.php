<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\System\Tenancy\{LandlordSchemaService, TenantConnectionManager};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{Artisan, Config, Crypt, DB};
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

final class CreateTenantCompany extends Command {

    protected $signature = 'tenant:create
        {slug : Subdominio/código único del cliente}
        {--domain= : Dominio completo. Si se omite usa slug + TENANCY_BASE_DOMAIN}
        {--database= : Nombre de la base de datos tenant. Si se omite usa TENANT_DB_PREFIX + slug}
        {--company-id=1 : ID raíz de company dentro de la BD tenant}
        {--commercial-name= : Nombre comercial inicial}
        {--legal-name= : Razón social inicial}
        {--document-number=99999999999 : Documento inicial}
        {--force : Actualiza el registro landlord si ya existe}
        {--skip-migrate : Sólo crea DB y registry, sin ejecutar migraciones tenant}
        {--skip-cache-clear : No ejecuta optimize:clear al final}';

    protected $description = 'Crea y habilita una compañía tenant con base de datos, subdominio/dominio y configuración inicial.';

    public function handle(TenantConnectionManager $connectionManager): int {

        $slug = $this->normalizeSlug((string) $this->argument('slug'));
        $companyId = (int) $this->option('company-id');
        $databaseName = $this->normalizeDatabaseName((string) ($this->option('database') ?: config('tenancy.database_prefix', 'gympe_tenant_') . $slug));
        $domain = $this->normalizeDomain((string) ($this->option('domain') ?: $this->defaultDomain($slug)));

        if($companyId <= 0) {
            throw new InvalidArgumentException('company-id debe ser mayor a 0.');
        }

        LandlordSchemaService::ensure();
        $this->assertRegistryIsAvailable($slug, $domain);

        $tenant = null;

        try {
            $this->createDatabase($databaseName);
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

        return $database;

    }

    private function defaultDomain(string $slug): string {

        $baseDomain = trim((string) config('tenancy.base_domain'));
        return $baseDomain !== '' ? "{$slug}.{$baseDomain}" : $slug;

    }

    private function assertRegistryIsAvailable(string $slug, string $domain): void {

        if($this->option('force')) {
            return;
        }

        $slugExists = DB::connection('landlord')
            ->table('tenant_databases')
            ->where('slug', $slug)
            ->exists();

        if($slugExists) {
            throw new InvalidArgumentException('Ya existe un tenant con ese subdominio/slug.');
        }

        $domainExists = DB::connection('landlord')
            ->table('tenant_domains')
            ->where('domain', $domain)
            ->exists();

        if($domainExists) {
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
            'connection_name' => config('tenancy.tenant_connection', 'tenant'),
            'database_name' => $databaseName,
            'db_driver' => 'mysql',
            'db_host' => env('TENANT_DB_HOST', env('DB_HOST', '127.0.0.1')),
            'db_port' => env('TENANT_DB_PORT', env('DB_PORT', '3306')),
            'db_username' => env('TENANT_DB_USERNAME', env('DB_USERNAME')),
            'db_password' => env('TENANT_DB_PASSWORD', env('DB_PASSWORD')) !== null
                ? Crypt::encryptString((string) env('TENANT_DB_PASSWORD', env('DB_PASSWORD')))
                : null,
            'status' => 'provisioning',
            'updated_at' => now()
        ];

        DB::connection('landlord')->table('tenant_databases')->updateOrInsert(
            ['slug' => $slug],
            $tenantPayload + ['slug' => $slug, 'created_at' => now()]
        );

        $tenant = \App\Models\System\Tenancy\TenantDatabase::query()
            ->where('slug', $slug)
            ->firstOrFail();

        DB::connection('landlord')->table('tenant_domains')->updateOrInsert(
            ['domain' => $domain],
            [
                'tenant_database_id' => $tenant->id,
                'domain' => $domain,
                'type' => Str::endsWith($domain, '.' . config('tenancy.base_domain')) ? 'subdomain' : 'custom',
                'is_primary' => true,
                'status' => 'active',
                'updated_at' => now(),
                'created_at' => now()
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
