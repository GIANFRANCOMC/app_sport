<?php

declare(strict_types=1);

namespace App\Services\System\Sales;

use stdClass;

use App\Models\System\Catalogs\Item;
use App\Models\System\Customers\Customer;
use App\Models\System\Sales\SaleHeader;
use App\Services\System\Base\{
    BaseConfigService,
    CompanyReferenceDataService,
    MasterReferenceDataService
};

final class SaleConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "sale";

    }

    protected static function cachePages(): array {

        return ["main", "list"];

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        $references = CompanyReferenceDataService::for($companyId);

        if($page === "list") {

            return self::data([
                "branches" => self::data([
                    "records" => $references->branchesWithSeries()
                ]),
                "customers" => self::data([
                    "records" => $references->customers()
                ]),
                "salesHeader" => self::data([
                    "statuses" => SaleHeader::getStatuses()
                ])
            ]);

        }

        return self::data([
            "branches" => self::data([
                "records" => $references->branchesWithSeries()
            ]),
            "currencies" => self::data([
                "records" => MasterReferenceDataService::currencies()
            ]),
            "customers" => self::data([
                "records"               => $references->activeCustomers(),
                "identityDocumentTypes" => MasterReferenceDataService::customerIdentityDocuments(),
                "genders"               => Customer::getGenders(),
                "statuses"              => Customer::getStatuses()
            ]),
            "items" => self::data([
                "durationTypes" => Item::getDurationTypes(),
                "records"       => $references->saleItems()
            ]),
            "taxes" => self::data([
                "records" => $references->taxesFor("sale")
            ]),
            "paymentMethods" => self::data([
                "records" => $references->paymentMethodsFor("sale")
            ]),
            "salesHeader" => self::data([
                "statuses" => SaleHeader::getStatuses()
            ])
        ]);

    }

}
