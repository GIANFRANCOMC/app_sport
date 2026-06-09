<?php

declare(strict_types=1);

namespace App\Services\System\Customers\Tracking;

use stdClass;

use App\Services\System\Base\{
    BaseConfigService,
    CompanyReferenceDataService
};

final class TrackingSubscriptionConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "tracking_subscription";

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        $references = CompanyReferenceDataService::for($companyId);

        return self::data([
            "branches" => self::data([
                "records" => $references->activeBranches()
            ]),
            "customers" => self::data([
                "records" => $references->activeCustomers()
            ])
        ]);

    }

}
