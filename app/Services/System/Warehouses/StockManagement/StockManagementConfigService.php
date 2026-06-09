<?php

declare(strict_types=1);

namespace App\Services\System\Warehouses\StockManagement;

use stdClass;

use App\Services\System\Base\{
    BaseConfigService,
    CompanyReferenceDataService
};

final class StockManagementConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "stock_management";

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        return self::data([
            "warehouses" => self::data([
                "records" => CompanyReferenceDataService::for($companyId)->stockWarehouses()
            ])
        ]);

    }

}
