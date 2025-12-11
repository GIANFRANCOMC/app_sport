<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Branches;

use App\Models\System\Organizations\Branch;
use Illuminate\Support\Facades\Cache;
use stdClass;

/**
 * Service for managing Branch configuration and initialization parameters
 * Implements caching for better performance
 */
class BranchConfigService {

    private const CACHE_PREFIX = "branch_config";
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get initialization parameters for branch module
     *
     * @param int $companyId Company ID
     * @param string $page Page identifier (only used to determine what data to return, not for cache key)
     * @return stdClass
     */
    public static function getInitParams(int $companyId, string $page = ""): stdClass {

        $cacheKey = self::buildCacheKey($companyId);

        return Cache::remember($cacheKey, self::CACHE_TTL, function() use($page) {

            $initParams = new stdClass();

            $config = new stdClass();

            if($page === "main") {

                $config->branches = new stdClass();
                $config->branches->statuses = Branch::getStatuses();

            }

            $initParams->config = $config;
            $initParams->bool   = true;

            return $initParams;

        });

    }

    /**
     * Build cache key for branch configuration
     *
     * @param int $companyId Company ID
     * @return string
     */
    private static function buildCacheKey(int $companyId): string {

        return self::CACHE_PREFIX."_company_{$companyId}";

    }

    /**
     * Clear cache for branch configuration
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearCache(int $companyId): void {

        $cacheKey = self::buildCacheKey($companyId);
        Cache::forget($cacheKey);

    }

    /**
     * Clear all branch configuration cache for a company
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearAllCache(int $companyId): void {

        self::clearCache($companyId);

    }

}

