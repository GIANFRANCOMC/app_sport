<?php

declare(strict_types=1);

namespace App\Services\System\Finance;

use stdClass;

use App\Services\System\Base\{BaseConfigService, CompanyReferenceDataService};

final class CashRegisterConfigService extends BaseConfigService {

    protected const USER_SCOPED_CACHE = true;

    protected static function getCachePrefix(): string {

        return "cash_registers:v1";

    }

    protected static function buildConfig(int $companyId, string $page, ?int $userId = null): stdClass {

        $references = CompanyReferenceDataService::for($companyId, $userId);

        return self::data([
            "branches" => $references->activeBranches(),
            "registers" => $references->cashRegisters(),
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
