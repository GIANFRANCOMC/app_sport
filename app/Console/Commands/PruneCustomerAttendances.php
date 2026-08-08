<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\System\Tenancy\TenantDatabase;
use App\Services\System\Customers\Tracking\AttendanceMaintenanceService;
use App\Services\System\Tenancy\TenantAdministrationService;
use App\Services\System\Tenancy\TenantConnectionManager;
use Illuminate\Console\Command;
use Throwable;

final class PruneCustomerAttendances extends Command {
    protected $signature = "attendances:prune-customers
                            {--tenant= : Procesar únicamente el slug tenant indicado}
                            {--company= : Procesar únicamente una empresa}
                            {--months= : Meses de retención; mínimo 4}
                            {--limit=1000 : Máximo de asistencias por empresa}
                            {--dry-run : Solo cuenta registros elegibles}";

    protected $description = "Depura asistencias antiguas de clientes respetando la retención configurada";

    public function handle(
        TenantConnectionManager $connectionManager,
        TenantAdministrationService $administration
    ): int {

        $tenantSlug = $this->option("tenant");
        $companyId = $this->option("company");
        $months = $this->option("months") === null ? null : max(4, (int) $this->option("months"));
        $tenants = TenantDatabase::query()
            ->where("status", "active")
            ->when($tenantSlug, fn ($query) => $query->where("slug", $tenantSlug))
            ->orderBy("id")
            ->get();

        if ($tenants->isEmpty()) {

            $this->error("No existen tenants activos para procesar.");

            return self::FAILURE;

        }

        $rows = [];
        $hasFailure = false;

        foreach ($tenants as $tenant) {

            try {

                $connectionManager->connect($tenant);
                $summary = AttendanceMaintenanceService::pruneCustomerAttendances(
                    $companyId === null ? null : (int) $companyId,
                    $months,
                    max(1, (int) $this->option("limit")),
                    (bool) $this->option("dry-run")
                );
                $rows[] = [$tenant->slug, $summary["companies"], $summary["eligible"], $summary["deleted"], "OK"];
                $administration->audit($tenant, "prune_customer_attendances", "success", $summary, "scheduler");

            } catch (Throwable $exception) {

                $hasFailure = true;
                $rows[] = [$tenant->slug, 0, 0, 0, $exception->getMessage()];
                $administration->audit($tenant, "prune_customer_attendances", "failure", [
                    "error" => $exception->getMessage(),
                ], "scheduler");

            } finally {

                $connectionManager->disconnect();

            }

        }

        $this->table(["Tenant", "Empresas", "Elegibles", "Eliminadas", "Resultado"], $rows);

        return $hasFailure ? self::FAILURE : self::SUCCESS;

    }
}
