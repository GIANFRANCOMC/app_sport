<?php

declare(strict_types=1);

namespace App\Services\System\Devices\BiometricDevices;

use stdClass;

use App\Models\System\Devices\BiometricDevice;
use App\Services\System\Base\{
    BaseConfigService,
    CompanyReferenceDataService
};

final class BiometricDeviceConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "biometric_device";

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        return self::data([
            "branches" => self::data([
                "records" => CompanyReferenceDataService::for($companyId)->activeBranches()
            ]),
            "brands"   => BiometricDevice::getBrands(),
            "models"   => ["ZKTeco" => BiometricDevice::getModelsByBrand("ZKTeco")],
            "statuses" => BiometricDevice::getStatuses()
        ]);

    }

}
