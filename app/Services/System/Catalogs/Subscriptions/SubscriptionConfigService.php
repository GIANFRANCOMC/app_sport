<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Subscriptions;

use stdClass;

use App\Models\System\Catalogs\Item;
use App\Services\System\Base\{
    BaseConfigService,
    CompanyReferenceDataService,
    MasterReferenceDataService
};

final class SubscriptionConfigService extends BaseConfigService {

    protected const USER_SCOPED_CACHE = true;

    protected static function getCachePrefix(): string {

        return "subscription";

    }

    protected static function buildConfig(int $companyId, string $page, ?int $userId = null): stdClass {

        $references = CompanyReferenceDataService::for($companyId, $userId);

        return self::data([
            "categories" => self::data([
                "records" => $references->categories()
            ]),
            "currencies" => self::data([
                "records" => MasterReferenceDataService::currencies($companyId)
            ]),
            "internal_code_prefixes" => self::internalCodePrefixes($companyId),
            "durationTypes" => Item::getDurationTypes(),
            "statuses"      => Item::getStatuses()
        ]);

    }

}
