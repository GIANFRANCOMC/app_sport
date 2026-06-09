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

    protected static function buildConfig(int $companyId, string $page): stdClass {

        return self::data([
            "identityDocumentTypes" => self::data([
                "records" => MasterReferenceDataService::defaultIdentityDocuments()
            ]),
            "roles" => self::data([
                "records" => CompanyReferenceDataService::for($companyId)->roles()
            ]),
            "genders"  => User::getGenders(),
            "statuses" => User::getStatuses()
        ]);

    }

}
