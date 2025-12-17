<?php

declare(strict_types=1);

namespace App\Services\System\Customers\Tracking;

use App\Models\System\Customers\Customer;
use Illuminate\Support\Facades\Cache;
use stdClass;

/**
 * Service for managing Tracking Customer configuration and initialization parameters
 * Implements caching for better performance
 */
class TrackingCustomerConfigService {

    private const CACHE_PREFIX = "tracking_customer_config";
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get initialization parameters for tracking customer module
     *
     * @param int $companyId Company ID
     * @param string $page Page identifier
     * @return stdClass
     */
    public static function getInitParams(int $companyId, string $page = ""): stdClass {

        $cacheKey = self::buildCacheKey($companyId, $page);

        return Cache::remember($cacheKey, self::CACHE_TTL, function() use($page, $companyId) {

            $initParams = new stdClass();

            $config = new stdClass();

            if($page === "main") {

                $config->customers = new stdClass();
                $config->customers->records = Customer::getAll("default", $companyId);

            }

            $initParams->config = $config;
            $initParams->bool   = true;

            return $initParams;

        });

    }

    /**
     * Build cache key for tracking customer configuration
     *
     * @param int $companyId Company ID
     * @param string $page Page identifier
     * @return string
     */
    private static function buildCacheKey(int $companyId, string $page): string {

        return self::CACHE_PREFIX."_company_{$companyId}_page_{$page}";

    }

    /**
     * Clear cache for tracking customer configuration
     *
     * @param int $companyId Company ID
     * @param string|null $page Page identifier (optional)
     * @return void
     */
    public static function clearCache(int $companyId, ?string $page = null): void {

        if($page) {

            $cacheKey = self::buildCacheKey($companyId, $page);
            Cache::forget($cacheKey);

        }else {

            Cache::forget(self::buildCacheKey($companyId, "main"));

        }

    }

    /**
     * Clear all tracking customer configuration cache for a company
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearAllCache(int $companyId): void {

        self::clearCache($companyId);

    }

}

