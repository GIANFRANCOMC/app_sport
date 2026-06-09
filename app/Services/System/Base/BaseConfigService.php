<?php

declare(strict_types=1);

namespace App\Services\System\Base;

use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;
use stdClass;

/**
 * Shared cache contract for System module initialization parameters.
 */
abstract class BaseConfigService {

    protected const CACHE_TTL = 3600;

    abstract protected static function getCachePrefix(): string;

    abstract protected static function buildConfig(int $companyId, string $page): stdClass;

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

    public static function cacheKey(int $companyId, string $page = "main"): string {

        self::validateCompanyId($companyId);

        $page = self::normalizePage($page);

        return sprintf(
            "init_params:%s:company:%d:page:%s",
            static::getCachePrefix(),
            $companyId,
            $page
        );

    }

    protected static function data(array $attributes = []): stdClass {

        return (object) $attributes;

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
