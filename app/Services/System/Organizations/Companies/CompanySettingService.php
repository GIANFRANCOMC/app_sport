<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Companies;

use App\Models\System\Organizations\CompanySetting;
use Illuminate\Support\Facades\Schema;

final class CompanySettingService {

    public const INTERNAL_CODE_PREFIXES = "internal_code_prefixes";
    public const INVENTORY_POLICIES = "inventory";

    private const DEFAULT_INTERNAL_CODE_PREFIXES = [
        "product" => "PRO",
        "service" => "SER",
        "subscription" => "MEM",
        "brand" => "MAR",
        "category" => "CAT",
        "branch" => "SUC",
        "asset" => "ACT"
    ];

    private const DEFAULT_INVENTORY_POLICIES = [
        "allow_negative_stock_on_sale" => false,
        "restore_stock_on_sale_cancellation" => false,
        "valuation_method" => "weighted_average"
    ];

    public static function group(int $companyId, string $group): array {

        $values = match($group) {
            self::INTERNAL_CODE_PREFIXES => self::DEFAULT_INTERNAL_CODE_PREFIXES,
            self::INVENTORY_POLICIES => self::DEFAULT_INVENTORY_POLICIES,
            default => []
        };

        if(!Schema::hasTable("company_settings")) {

            return $values;

        }

        $settings = CompanySetting::query()
                                  ->where("company_id", $companyId)
                                  ->where("group", $group)
                                  ->where("status", "active")
                                  ->orderBy("id")
                                  ->get(["key", "value", "value_type"]);

        foreach($settings as $setting) {

            $values[$setting->key] = self::castValue($setting->value, $setting->value_type);

        }

        return $values;

    }

    public static function value(int $companyId, string $group, string $key, mixed $default = null): mixed {

        $values = self::group($companyId, $group);

        return array_key_exists($key, $values) ? $values[$key] : $default;

    }

    private static function castValue(?string $value, string $type): mixed {

        return match($type) {
            "boolean" => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            "integer" => $value === null ? null : (int) $value,
            "decimal" => $value === null ? null : (float) $value,
            "json" => $value === null ? null : json_decode($value, true),
            default => $value
        };

    }

}
