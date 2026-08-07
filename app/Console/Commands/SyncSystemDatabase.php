<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\System\Database\SystemCatalogSyncService;
use Illuminate\Console\Command;

final class SyncSystemDatabase extends Command
{
    protected $signature = 'system:sync {--company= : Limita la sincronización a una organización}';
    protected $description = 'Proyecta el menú almacenado en la base de datos hacia organizaciones y permisos administrativos.';

    public function handle(SystemCatalogSyncService $service): int
    {
        $companyId = $this->option('company');
        $result = $service->sync($companyId !== null ? (int) $companyId : null);
        $this->components->info(sprintf(
            'Menú proyectado desde la base de datos: %d categorías, %d secciones, %d grupos, %d opciones y %d organizaciones.',
            $result['categories'], $result['sections'], $result['groups'], $result['items'], $result['companies']
        ));
        return self::SUCCESS;
    }
}
