<?php

declare(strict_types=1);

namespace App\Services\System\Base;

use Illuminate\Support\Facades\{Auth, Cache};
use InvalidArgumentException;
use stdClass;
use App\Services\System\Organizations\Companies\CompanySettingService;

/**
 * Shared cache contract for System module initialization parameters.
 */
abstract class BaseConfigService {

    protected const CACHE_TTL = 3600;

    abstract protected static function getCachePrefix(): string;

    abstract protected static function buildConfig(int $companyId, string $page): stdClass;

    protected static function usesUserScopedCache(): bool {

        return false;

    }

    /**
     * Pages whose configuration can be cached by the module.
     *
     * @return array<int, string>
     */
    protected static function cachePages(): array {

        return ["main"];

    }

    public static function getInitParams(int $companyId, string $page = "main"): stdClass {

        self::validateCompanyId($companyId);

        $page = self::normalizePage($page);

        return Cache::remember(
            static::cacheKey($companyId, $page),
            static::CACHE_TTL,
            fn() => static::createInitParams(static::buildConfig($companyId, $page))
        );

    }

    public static function clearCache(int $companyId, ?string $page = null): void {

        self::validateCompanyId($companyId);

        $pages = $page === null
            ? static::cachePages()
            : [self::normalizePage($page)];

        foreach(array_unique($pages) as $cachePage) {

            Cache::forget(static::cacheKey($companyId, $cachePage));

        }

    }

    public static function clearAllCache(int $companyId): void {

        static::clearCache($companyId);

    }

    public static function clearUserCache(int $companyId, int $userId, ?string $page = null): void {

        self::validateCompanyId($companyId);

        if(!static::usesUserScopedCache() || $userId <= 0) {

            return;

        }

        $pages = $page === null
            ? static::cachePages()
            : [self::normalizePage($page)];

        foreach(array_unique($pages) as $cachePage) {

            Cache::forget(static::cacheKey($companyId, $cachePage, $userId));

        }

    }

    public static function cacheKey(int $companyId, string $page = "main", ?int $userId = null): string {

        self::validateCompanyId($companyId);

        $page = self::normalizePage($page);

        $cacheKey = sprintf(
            "init_params:%s:company:%d:page:%s",
            static::getCachePrefix(),
            $companyId,
            $page
        );

        if(static::usesUserScopedCache()) {

            $cacheKey .= sprintf(":user:%d", (int) ($userId ?? Auth::id()));

        }

        return $cacheKey;

    }

    protected static function data(array $attributes = []): stdClass {

        return (object) $attributes;

    }

    protected static function internalCodePrefixes(int $companyId): array {

        return CompanySettingService::group(
            $companyId,
            CompanySettingService::INTERNAL_CODE_PREFIXES
        );

    }

    private static function createInitParams(stdClass $config): stdClass {

        return self::data([
            "config" => $config,
            "bool"   => true
        ]);

    }

    private static function normalizePage(string $page): string {

        $page = strtolower(trim($page));
        $pages = static::cachePages();

        if($page === "") {

            return $pages[0] ?? "main";

        }

        return in_array($page, $pages, true)
            ? $page
            : ($pages[0] ?? "main");

    }

    private static function validateCompanyId(int $companyId): void {

        if($companyId <= 0) {

            throw new InvalidArgumentException("Company ID must be greater than zero.");

        }

    }

}
