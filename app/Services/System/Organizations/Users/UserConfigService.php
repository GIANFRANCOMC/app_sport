<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Users;

use stdClass;

use App\Models\System\Organizations\User;
use App\Services\System\Base\{
    BaseConfigService,
    CompanyReferenceDataService,
    MasterReferenceDataService
};

final class UserConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "user";

    }

    protected static function usesUserScopedCache(): bool {

        return true;

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        $references = CompanyReferenceDataService::for($companyId);

        return self::data([
            "identityDocumentTypes" => self::data([
                "records" => MasterReferenceDataService::defaultIdentityDocuments($companyId)
            ]),
            "roles" => self::data([
                "records" => $references->roles()
            ]),
            "branches" => self::data([
                "records" => $references->activeBranches()
            ]),
            "genders"  => User::getGenders(),
            "statuses" => User::getStatuses()
        ]);

    }

}
