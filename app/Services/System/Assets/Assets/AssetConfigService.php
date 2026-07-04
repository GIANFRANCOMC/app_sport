<?php

declare(strict_types=1);

namespace App\Services\System\Assets\Assets;

use stdClass;

use App\Models\System\Assets\{Asset, AssetCategory};
use App\Services\System\Base\BaseConfigService;

final class AssetConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "asset";

    }

    protected static function buildConfig(int $companyId, string $page, ?int $userId = null): stdClass {

        return self::data([
            "internal_code_prefixes" => self::internalCodePrefixes($companyId),
            "categories" => AssetCategory::query()
                ->where("company_id", $companyId)
                ->where("status", "active")
                ->orderBy("name")
                ->get(["id", "name"]),
            "statuses" => Asset::getStatuses()
        ]);

    }

}
