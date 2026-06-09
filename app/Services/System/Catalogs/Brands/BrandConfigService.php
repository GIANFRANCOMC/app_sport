<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Brands;

use App\Models\System\Catalogs\Brand;
use App\Services\System\Base\BaseConfigService;
use stdClass;

final class BrandConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "brand";

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        return self::data([
            "statuses" => Brand::getStatuses()
        ]);

    }

}
