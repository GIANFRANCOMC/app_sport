<?php

declare(strict_types=1);

namespace App\Services\System\Organizations;

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
     * @param string $page Page identifier
     * @return stdClass
     */
    public static function getInitParams(string $page = ""): stdClass {

        $cacheKey = self::CACHE_PREFIX . "_init_" . $page;

        return Cache::remember($cacheKey, self::CACHE_TTL, function() use($page) {

            $initParams = new stdClass();
            $config = new stdClass();

            if($page === "main") {
                $config->branches = new stdClass();
                $config->branches->statuses = Branch::getStatuses();
            }

            $initParams->config = $config;
            $initParams->bool = true;

            return $initParams;

        });

    }

    /**
     * Clear cache for branch configuration
     *
     * @param string $page Page identifier
     * @return void
     */
    public static function clearCache(string $page = ""): void {

        $cacheKey = self::CACHE_PREFIX . "_init_" . $page;
        Cache::forget($cacheKey);

    }

    /**
     * Clear all branch configuration cache
     *
     * @return void
     */
    public static function clearAllCache(): void {

        Cache::forget(self::CACHE_PREFIX . "_init_main");

    }

}
