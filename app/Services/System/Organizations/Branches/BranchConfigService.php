<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Branches;

use stdClass;

use App\Models\System\Organizations\Branch;
use App\Services\System\Base\BaseConfigService;

final class BranchConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "branch";

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        return self::data([
            "statuses" => Branch::getStatuses()
        ]);

    }

}
