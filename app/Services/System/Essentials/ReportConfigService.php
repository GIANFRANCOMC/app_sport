<?php

declare(strict_types=1);

namespace App\Services\System\Essentials;

use stdClass;

use App\Services\System\Base\BaseConfigService;

final class ReportConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "report";

    }

    protected static function buildConfig(int $companyId, string $page, ?int $userId = null): stdClass {

        return self::data();

    }

}
