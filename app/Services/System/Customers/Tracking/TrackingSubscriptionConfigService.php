<?php

declare(strict_types=1);

namespace App\Services\System\Customers\Tracking;

use App\Services\System\Base\BaseConfigService;
use App\Services\System\Base\CompanyReferenceDataService;
use stdClass;

final class TrackingSubscriptionConfigService extends BaseConfigService {
    protected const USER_SCOPED_CACHE = true;

    protected static function getCachePrefix(): string {

        return "tracking_subscription";

    }

    protected static function buildConfig(int $companyId, string $page, ?int $userId = null): stdClass {

        $references = CompanyReferenceDataService::for($companyId, $userId);

        return self::data([
            "branches" => self::data([
                "records" => $references->activeBranches(),
            ]),
            "customers" => self::data([
                "records" => $references->activeCustomers(),
            ]),
            "subscription_items" => self::data([
                "records" => $references->subscriptionItems(),
            ]),
        ]);

    }
}
