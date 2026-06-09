<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Services;

use stdClass;

use App\Models\System\Catalogs\Item;
use App\Services\System\Base\{
    BaseConfigService,
    CompanyReferenceDataService,
    MasterReferenceDataService
};

final class ServiceConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "service";

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        $references = CompanyReferenceDataService::for($companyId);

        return self::data([
            "categories" => self::data([
                "records" => $references->categories()
            ]),
            "currencies" => self::data([
                "records" => MasterReferenceDataService::currencies()
            ]),
            "statuses" => Item::getStatuses()
        ]);

    }

}
