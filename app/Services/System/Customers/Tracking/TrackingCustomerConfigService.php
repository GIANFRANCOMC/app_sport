<?php

declare(strict_types=1);

namespace App\Services\System\Customers\Tracking;

use stdClass;

use App\Services\System\Base\{
    BaseConfigService,
    CompanyReferenceDataService
};

final class TrackingCustomerConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "tracking_customer";

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        return self::data([
            "customers" => self::data([
                "records" => CompanyReferenceDataService::for($companyId)->customers()
            ])
        ]);

    }

}
