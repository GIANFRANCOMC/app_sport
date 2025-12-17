<?php

declare(strict_types=1);

namespace App\Services\System\Essentials;

use Illuminate\Support\Facades\Cache;
use stdClass;

/**
 * Service for managing Dashboard configuration and initialization parameters
 * Implements caching for better performance
 */
class DashboardConfigService {

    private const CACHE_PREFIX = "dashboard_config";
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get initialization parameters for dashboard module
     *
     * @param int $companyId Company ID
     * @param string $page Page identifier
     * @return stdClass
     */
    public static function getInitParams(int $companyId, string $page = ""): stdClass {

        $cacheKey = self::buildCacheKey($companyId, $page);

        return Cache::remember($cacheKey, self::CACHE_TTL, function() use($page) {

            $initParams = new stdClass();

            $config = new stdClass();

            if($page === "main") {

                // Dashboard configuration can be added here if needed

            }

            $initParams->config = $config;
            $initParams->bool   = true;

            return $initParams;

        });

    }

    /**
     * Clear all cache for dashboard module
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearAllCache(int $companyId): void {

        $pages = ["main"];

        foreach($pages as $page) {

            $cacheKey = self::buildCacheKey($companyId, $page);
            Cache::forget($cacheKey);

        }

    }

    /**
     * Build cache key for dashboard module
     *
     * @param int $companyId Company ID
     * @param string $page Page identifier
     * @return string
     */
    private static function buildCacheKey(int $companyId, string $page = ""): string {

        return self::CACHE_PREFIX."_{$companyId}_{$page}";

    }

}

