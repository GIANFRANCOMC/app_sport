<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Services;

use Illuminate\Support\Facades\Cache;
use stdClass;

use App\Models\System\Catalogs\{Category, Item};
use App\Models\System\General\Currency;

/**
 * Service for managing module configuration and initialization parameters
 * Implements caching for better performance
 */
class ServiceConfigService {

    private const CACHE_PREFIX = "service_config";
    private const CACHE_TTL    = 3600; // 1 hour

    /**
     * Get initialization parameters for module
     *
     * @param int $companyId Company
     * @param string $page Page (only used to determine what data to return, not for cache key)
     * @return stdClass
     */
    public static function getInitParams(int $companyId, string $page = ""): stdClass {

        $cacheKey = self::buildCacheKey($companyId);

        return Cache::remember($cacheKey, self::CACHE_TTL, function() use($page, $companyId) {

            $initParams = new stdClass();

            $config = new stdClass();

            if($page === "main") {

                $config->services = new stdClass();
                $config->services->statuses = Item::getStatuses();

                $config->categories = new stdClass();
                $config->categories->records = Category::getAll("service", $companyId);

                $config->currencies = new stdClass();
                $config->currencies->records = Currency::get();

            }

            $initParams->config = $config;
            $initParams->bool   = true;

            return $initParams;

        });

    }

    /**
     * Build cache key for module configuration
     *
     * @param int $companyId Company
     * @return string
     */
    private static function buildCacheKey(int $companyId): string {

        return self::CACHE_PREFIX."_company_{$companyId}";

    }

    /**
     * Clear cache for module configuration
     *
     * @param int $companyId Company
     * @return void
     */
    public static function clearCache(int $companyId): void {

        $cacheKey = self::buildCacheKey($companyId);

        Cache::forget($cacheKey);

    }

    /**
     * Clear all module configuration cache for a company
     *
     * @param int $companyId Company
     * @return void
     */
    public static function clearAllCache(int $companyId): void {

        self::clearCache($companyId);

    }

}
