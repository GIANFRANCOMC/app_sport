<?php

declare(strict_types=1);

namespace App\Services\System\Base;

use App\Services\System\Organizations\Companies\{CompanySettingService};

final class InternalCodeService {
    public static function prefix(int $companyId, string $entity): string {

        $prefix = CompanySettingService::value(
            $companyId,
            CompanySettingService::INTERNAL_CODE_PREFIXES,
            $entity,
            ""
        );

        return strtoupper(trim((string) $prefix, " \t\n\r\0\x0B-"));

    }

    public static function applyPrefix(int $companyId, string $entity, mixed $code): string {

        $code = trim((string) $code);
        $prefix = self::prefix($companyId, $entity);

        if($prefix === "" || $code === "") {

            return $code;

        }

        $prefixWithSeparator = "{$prefix}-";

        return str_starts_with(strtoupper($code), $prefixWithSeparator)
            ? $code
            : "{$prefixWithSeparator}{$code}";

    }
}
