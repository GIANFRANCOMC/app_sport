<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Categories;

use App\Models\System\Catalogs\Category;
use Illuminate\Support\Facades\Cache;
use stdClass;

/**
 * Service for managing Category configuration and initialization parameters
 * Implements caching for better performance
 */
class CategoryConfigService {

    private const CACHE_PREFIX = "category_config";
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get initialization parameters for category module
     *
     * @param int $companyId Company ID
     * @param string $page Page identifier
     * @return stdClass
     */
    public static function getInitParams(int $companyId, string $page = ""): stdClass {

        $cacheKey = self::buildCacheKey($companyId);

        return Cache::remember($cacheKey, self::CACHE_TTL, function() use($page) {

            $initParams = new stdClass();

            $config = new stdClass();

            if($page === "main") {

                $config->categories = new stdClass();
                $config->categories->statuses = Category::getStatuses();

            }

            $initParams->config = $config;
            $initParams->bool   = true;

            return $initParams;

        });

    }

    /**
     * Build cache key for category configuration
     *
     * @param int $companyId Company ID
     * @return string
     */
    private static function buildCacheKey(int $companyId): string {

        return self::CACHE_PREFIX."_company_{$companyId}";

    }

    /**
     * Clear cache for category configuration
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearCache(int $companyId): void {

        $cacheKey = self::buildCacheKey($companyId);
        Cache::forget($cacheKey);

    }

    /**
     * Clear all category configuration cache for a company
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearAllCache(int $companyId): void {

        self::clearCache($companyId);

    }

}

