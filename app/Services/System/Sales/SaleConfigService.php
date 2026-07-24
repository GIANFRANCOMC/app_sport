<?php

declare(strict_types=1);

namespace App\Services\System\Sales;

use stdClass;

use App\Models\System\Catalogs\Item;
use App\Models\System\Customers\Customer;
use App\Models\System\Finance\CashSession;
use App\Models\System\Sales\{QuotationHeader, SaleHeader};
use App\Services\System\Base\{
    BaseConfigService,
    CompanyReferenceDataService,
    MasterReferenceDataService
};

final class SaleConfigService extends BaseConfigService {

    protected const USER_SCOPED_CACHE = true;

    protected static function getCachePrefix(): string {

        return "sale";

    }

    protected static function cachePages(): array {

        return ["main", "list", "deliveries"];

    }

    protected static function buildConfig(int $companyId, string $page, ?int $userId = null): stdClass {

        $references = CompanyReferenceDataService::for($companyId, $userId);

        if($page === "list" || $page === "deliveries") {

            return self::data([
                "branches" => self::data([
                    "records" => $references->branchesWithSeries()
                ]),
                "warehouses" => self::data([
                    "records" => $references->stockWarehouses()
                ]),
                "customers" => self::data([
                    "records" => $references->customers()
                ]),
                "salesHeader" => self::data([
                    "statuses" => SaleHeader::getStatuses()
                ]),
                "saleDeliveries" => self::data([
                    "statuses" => \App\Models\System\Sales\SaleDelivery::getStatuses()
                ])
            ]);

        }

        $cashSessions = CashSession::query()
                                   ->with(["register", "branch"])
                                   ->where("company_id", $companyId)
                                   ->where("status", "open");

        $cashRegisterIds = $references->allowedCashRegisterIds();
        if($cashRegisterIds !== null) {

            $cashSessions->whereIn("cash_register_id", $cashRegisterIds);

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
            "users" => self::data([
                "records" => $references->users(),
                "current_id" => $userId
            ]),
            "cashSessions" => self::data([
                "records" => $cashSessions->latest("opened_at")->get()
            ]),
            "quotations" => self::data([
                "records" => QuotationHeader::query()
                    ->where("company_id", $companyId)
                    ->whereIn("status", ["draft", "sent", "accepted"])
                    ->with("holder:id,name,document_number")
                    ->latest("id")
                    ->limit(100)
                    ->get(["id", "reference", "holder_id", "issue_date", "valid_until", "total", "status"])
            ]),
            "salesHeader" => self::data([
                "statuses" => SaleHeader::getStatuses()
            ])
        ]);

    }

}
