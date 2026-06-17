<?php

declare(strict_types=1);

namespace App\Services\System\Finance;

use stdClass;

use App\Models\System\Finance\CashRegister;
use App\Services\System\Base\{BaseConfigService, CompanyReferenceDataService};

final class CashRegisterConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "cash_registers:v1";

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        $references = CompanyReferenceDataService::for($companyId);

        return self::data([
            "branches" => $references->activeBranches(),
            "registers" => CashRegister::query()
                                       ->with("branch")
                                       ->where("company_id", $companyId)
                                       ->where("status", "active")
                                       ->orderBy("name")
                                       ->get(),
            "paymentMethods" => $references->paymentMethodsFor("sale"),
            "statuses" => [
                ["id" => "open", "label" => "Abierta"],
                ["id" => "closed", "label" => "Cerrada"],
                ["id" => "cancelled", "label" => "Anulada"]
            ],
            "movementTypes" => [
                ["id" => "opening", "label" => "Apertura"],
                ["id" => "sale", "label" => "Venta"],
                ["id" => "income", "label" => "Ingreso"],
                ["id" => "expense", "label" => "Salida"],
                ["id" => "adjustment", "label" => "Ajuste"],
                ["id" => "closing", "label" => "Cierre"]
            ]
        ]);

    }

}
