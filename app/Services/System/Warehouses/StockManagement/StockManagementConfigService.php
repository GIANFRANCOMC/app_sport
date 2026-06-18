<?php

declare(strict_types=1);

namespace App\Services\System\Warehouses\StockManagement;

use stdClass;
use App\Models\System\Catalogs\Item;

use App\Services\System\Base\{
    BaseConfigService,
    CompanyReferenceDataService
};

final class StockManagementConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "inventory_v2";

    }

    protected static function usesUserScopedCache(): bool {

        return true;

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        return self::data([
            "warehouses" => self::data([
                "records" => CompanyReferenceDataService::for($companyId)->stockWarehouses()
            ]),
            "products" => self::data([
                "records" => Item::query()
                    ->where("company_id", $companyId)
                    ->where("type", "product")
                    ->where("status", "active")
                    ->select(["id", "internal_code", "barcode", "name"])
                    ->orderBy("name")
                    ->get()
            ])
        ]);

    }

}
