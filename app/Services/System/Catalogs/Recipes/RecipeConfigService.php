<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Recipes;

use stdClass;

use App\Models\System\Catalogs\{Item, RecipeDish};
use App\Services\System\Base\{
    BaseConfigService,
    CompanyReferenceDataService,
    MasterReferenceDataService
};

final class RecipeConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "recipe";

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        $references = CompanyReferenceDataService::for($companyId);

        return self::data([
            "items" => self::data([
                "records" => $references->saleItems()
            ]),
            "ingredients" => self::data([
                "records" => Item::query()
                    ->where("company_id", $companyId)
                    ->where("type", "product")
                    ->where("status", "active")
                    ->with(["currency", "brand"])
                    ->orderBy("name")
                    ->get()
            ]),
            "currencies" => self::data([
                "records" => MasterReferenceDataService::currencies()
            ]),
            "internal_code_prefixes" => self::internalCodePrefixes($companyId),
            "statuses" => RecipeDish::getStatuses()
        ]);

    }

}
