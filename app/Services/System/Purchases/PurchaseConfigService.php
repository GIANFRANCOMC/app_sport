<?php

declare(strict_types=1);

namespace App\Services\System\Purchases;

use stdClass;

use App\Models\System\Catalogs\Item;
use App\Models\System\Purchases\Supplier;
use App\Services\System\Base\{
    BaseConfigService,
    CompanyReferenceDataService,
    MasterReferenceDataService
};

final class PurchaseConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "purchases_v1";

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        return self::data([
            "suppliers" => self::data([
                "records" => Supplier::query()
                    ->where("company_id", $companyId)
                    ->where("status", "active")
                    ->orderBy("name")
                    ->get()
            ]),
            "warehouses" => self::data([
                "records" => CompanyReferenceDataService::for($companyId)->stockWarehouses()
            ]),
            "currencies" => self::data([
                "records" => MasterReferenceDataService::currencies()
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
