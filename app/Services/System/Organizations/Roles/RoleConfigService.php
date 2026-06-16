<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Roles;

use App\Services\System\Base\BaseConfigService;
use App\Services\System\Organizations\Companies\CompanySectionService;
use App\Models\System\Organizations\Role;
use stdClass;

final class RoleConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "roles_v1";

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        return self::data([
            "sections" => self::data([
                "records" => CompanySectionService::getSections($companyId)
            ]),
            "statuses" => Role::getStatuses()
        ]);

    }

}
