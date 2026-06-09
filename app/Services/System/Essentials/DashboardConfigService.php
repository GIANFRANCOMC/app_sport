<?php

declare(strict_types=1);

namespace App\Services\System\Essentials;

use stdClass;

use App\Services\System\Base\BaseConfigService;

final class DashboardConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "dashboard";

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        return self::data();

    }

}
