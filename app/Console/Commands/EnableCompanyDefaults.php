<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\System\Database\SystemCatalogSyncService;
use App\Services\System\Organizations\Companies\CompanyProvisioningService;
use Illuminate\Console\Command;

final class EnableCompanyDefaults extends Command
{
    protected $signature = 'company:enable {company_id : ID de la organización} {--skip-modules : No habilita módulos ni permisos}';
    protected $description = 'Aprovisiona de forma idempotente los datos base de una organización.';

    public function handle(CompanyProvisioningService $provisioning, SystemCatalogSyncService $catalog): int
    {
        $companyId = (int) $this->argument('company_id');
        $catalog->sync($companyId);
        $provisioning->enable($companyId, !$this->option('skip-modules'));
        $this->components->info("Organización {$companyId} aprovisionada correctamente.");
        return self::SUCCESS;
    }
}
