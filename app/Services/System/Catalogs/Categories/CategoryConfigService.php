<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Categories;

use stdClass;

use App\Models\System\Catalogs\Category;
use App\Services\System\Base\BaseConfigService;

final class CategoryConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "category";

    }

    protected static function buildConfig(int $companyId, string $page, ?int $userId = null): stdClass {

        return self::data([
            "internal_code_prefixes" => self::internalCodePrefixes($companyId),
            "statuses" => Category::getStatuses()
        ]);

    }

}
