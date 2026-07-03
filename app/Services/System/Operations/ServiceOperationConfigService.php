<?php

declare(strict_types=1);

namespace App\Services\System\Operations;

use App\Services\System\Base\{BaseConfigService, CompanyReferenceDataService};
use stdClass;

final class ServiceOperationConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "service_operations";

    }

    protected static function cachePages(): array {

        return ["restaurant", "services"];

    }

    protected static function usesUserScopedCache(): bool {

        return true;

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        $references = CompanyReferenceDataService::for($companyId);

        return self::data([
            "page" => $page,
            "branches" => $references->activeBranches(),
            "users" => $references->users(),
            "customers" => $references->activeCustomers(),
            "items" => $references->saleItems(),
            "stationTypes" => ServiceOperationService::stationTypes(),
            "stationColors" => ServiceOperationService::stationColors(),
            "stationShapes" => ServiceOperationService::stationShapes(),
            "sessionTypes" => ServiceOperationService::sessionTypes(),
            "sessionStatuses" => ServiceOperationService::sessionStatuses()
        ]);

    }

}
