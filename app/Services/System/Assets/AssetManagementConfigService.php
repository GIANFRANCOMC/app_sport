<?php

declare(strict_types=1);

namespace App\Services\System\Assets;

use App\Models\System\Assets\AssetAssignment;
use App\Models\System\Assets\BranchAsset;
use App\Services\System\Base\BaseConfigService;
use App\Services\System\Base\CompanyReferenceDataService;
use stdClass;

final class AssetManagementConfigService extends BaseConfigService {
    protected const USER_SCOPED_CACHE = true;

    protected static function getCachePrefix(): string {

        return "asset_management";

    }

    protected static function buildConfig(int $companyId, string $page, ?int $userId = null): stdClass {

        $references = CompanyReferenceDataService::for($companyId, $userId);

        return self::data([
            "assets" => self::data([
                "records" => $references->assets(),
            ]),
            "branches" => self::data([
                "records" => $references->activeBranches(),
            ]),
            "users" => self::data([
                "records" => $references->users(),
            ]),
            "branchAssets" => self::data([
                "statuses" => BranchAsset::getStatuses(),
            ]),
            "assetAssignments" => self::data([
                "statuses" => AssetAssignment::getStatuses(),
            ]),
        ]);

    }
}
