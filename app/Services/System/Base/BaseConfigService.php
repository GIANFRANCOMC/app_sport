<?php

declare(strict_types=1);

namespace App\Services\System\Base;

use Illuminate\Support\Facades\Cache;
use stdClass;

/**
 * Base Config Service Class
 * Provides common functionality for all config service classes
 */
abstract class BaseConfigService {

    /**
     * Cache TTL in seconds (1 hour default)
     */
    protected const CACHE_TTL = 3600;

    /**
     * Cache prefix for the service
     * Must be defined in child classes
     *
     * @return string
     */
    abstract protected static function getCachePrefix(): string;

    /**
     * Build cache key
     *
     * @param int $companyId Company ID
     * @param string $page Page identifier
     * @return string
     */
    protected static function buildCacheKey(int $companyId, string $page = ""): string {

        $prefix = static::getCachePrefix();
        $key    = "{$prefix}_{$companyId}";

        if($page !== "") {

            $key .= "_{$page}";

        }

        return $key;

    }

    /**
     * Get cached data or execute callback
     *
     * @param string $cacheKey Cache key
     * @param callable $callback Callback to execute if cache miss
     * @param int|null $ttl Cache TTL (uses default if null)
     * @return mixed
     */
    protected static function remember(string $cacheKey, callable $callback, ?int $ttl = null) {

        return Cache::remember($cacheKey, $ttl ?? static::CACHE_TTL, $callback);

    }

    /**
     * Clear cache for company
     *
     * @param int $companyId Company ID
     * @param string $page Page identifier (optional)
     * @return void
     */
    public static function clearCache(int $companyId, string $page = ""): void {

        $cacheKey = static::buildCacheKey($companyId, $page);
        Cache::forget($cacheKey);

    }

    /**
     * Clear all cache for company (all pages)
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearAllCache(int $companyId): void {

        // Clear main cache
        static::clearCache($companyId);

        // Clear page-specific caches (common pages)
        $pages = ["main", "list", "create", "edit"];

        foreach($pages as $page) {

            static::clearCache($companyId, $page);

        }

    }

    /**
     * Create standard init params structure
     *
     * @param stdClass $config Config data
     * @return stdClass
     */
    protected static function createInitParams(stdClass $config): stdClass {

        $initParams        = new stdClass();
        $initParams->config = $config;
        $initParams->bool   = true;

        return $initParams;

    }

}

