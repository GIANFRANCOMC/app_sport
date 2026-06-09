<?php

declare(strict_types=1);

namespace App\Services\System\Customers\Customers;

use stdClass;

use App\Models\System\Customers\Customer;
use App\Services\System\Base\{
    BaseConfigService,
    MasterReferenceDataService
};
use App\Services\System\Devices\BiometricDevices\BiometricDeviceService;

final class CustomerConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "customer";

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        return self::data([
            "biometricDevices" => self::data([
                "records" => BiometricDeviceService::getActiveDevices($companyId)
            ]),
            "identityDocumentTypes" => self::data([
                "records" => MasterReferenceDataService::customerIdentityDocuments()
            ]),
            "genders"  => Customer::getGenders(),
            "statuses" => Customer::getStatuses()
        ]);

    }

}
