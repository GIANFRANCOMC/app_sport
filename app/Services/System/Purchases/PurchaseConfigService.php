<?php

declare(strict_types=1);

namespace App\Services\System\Purchases;

use stdClass;

use App\Models\System\Catalogs\Item;
use App\Models\System\Purchases\{PurchaseHeader, Supplier};
use App\Services\System\Base\{
    BaseConfigService,
    CompanyReferenceDataService,
    MasterReferenceDataService
};
use App\Services\System\Organizations\Companies\CompanySettingService;

final class PurchaseConfigService extends BaseConfigService {

    protected const USER_SCOPED_CACHE = true;

    protected static function getCachePrefix(): string {

        return "purchases";

    }

    protected static function buildConfig(int $companyId, string $page, ?int $userId = null): stdClass {

        $references = CompanyReferenceDataService::for($companyId, $userId);

        return self::data([
            "suppliers" => self::data([
                "records" => Supplier::query()
                    ->where("company_id", $companyId)
                    ->where("status", "active")
                    ->orderBy("name")
                    ->get()
            ]),
            "branches" => self::data([
                "records" => $references->activeBranches()
            ]),
            "warehouses" => self::data([
                "records" => $references->stockWarehouses()
            ]),
            "currencies" => self::data([
                "records" => MasterReferenceDataService::currencies($companyId)
            ]),
            "products" => self::data([
                "records" => Item::query()
                    ->where("company_id", $companyId)
                    ->where("type", "product")
                    ->where("status", "active")
                    ->select(["id", "internal_code", "barcode", "name"])
                    ->orderBy("name")
                    ->get()
            ]),
            "purchaseDocumentTypes" => self::data([
                "records" => PurchaseHeader::getDocumentTypes()
            ]),
            "purchaseDeliveryModes" => self::data([
                "records" => PurchaseHeader::getDeliveryModes()
            ]),
            "purchasePaymentModalities" => self::data([
                "records" => PurchaseHeader::getPaymentModalities()
            ]),
            "taxes" => self::data([
                "records" => $references->taxesFor("purchase")
            ]),
            "paymentMethods" => self::data([
                "records" => $references->paymentMethodsFor("purchase")
            ]),
            "settings" => self::data([
                "payment" => CompanySettingService::group($companyId, CompanySettingService::PURCHASES)
            ])
        ]);

    }

}
