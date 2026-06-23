<?php

declare(strict_types=1);

namespace App\Services\System\Sales;

use stdClass;

use App\Models\System\Catalogs\Item;
use App\Models\System\Customers\Customer;
use App\Models\System\Finance\CashSession;
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

    protected static function usesUserScopedCache(): bool {

        return true;

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

        $cashSessions = CashSession::query()
                                   ->with(["register", "branch"])
                                   ->where("company_id", $companyId)
                                   ->where("status", "open");

        if($branchIds = $references->allowedBranchIds()) {

            $cashSessions->whereIn("branch_id", $branchIds);

        }

        return self::data([
            "branches" => self::data([
                "records" => $references->branchesWithSeries()
            ]),
            "warehouses" => self::data([
                "records" => $references->stockWarehouses()
            ]),
            "currencies" => self::data([
                "records" => MasterReferenceDataService::currencies($companyId)
            ]),
            "customers" => self::data([
                "records"               => $references->activeCustomers(),
                "identityDocumentTypes" => MasterReferenceDataService::customerIdentityDocuments($companyId),
                "genders"               => Customer::getGenders(),
                "statuses"              => Customer::getStatuses()
            ]),
            "items" => self::data([
                "durationTypes" => Item::getDurationTypes(),
                "records"       => $references->saleItems()
            ]),
            "categories" => self::data([
                "records" => $references->categories()
            ]),
            "taxes" => self::data([
                "records" => $references->taxesFor("sale")
            ]),
            "paymentMethods" => self::data([
                "records" => $references->paymentMethodsFor("sale")
            ]),
            "cashSessions" => self::data([
                "records" => $cashSessions->latest("opened_at")->get()
            ]),
            "salesHeader" => self::data([
                "statuses" => SaleHeader::getStatuses()
            ])
        ]);

    }

}
