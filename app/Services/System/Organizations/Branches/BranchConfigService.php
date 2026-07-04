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

    protected static function buildConfig(int $companyId, string $page, ?int $userId = null): stdClass {

        return self::data([
            "internal_code_prefixes" => self::internalCodePrefixes($companyId),
            "statuses" => Branch::getStatuses()
        ]);

    }

}
