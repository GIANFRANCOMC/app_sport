<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Items;

use App\Models\System\Catalogs\{Category, Item};
use App\Models\System\General\Currency;
use Illuminate\Support\Facades\Cache;
use stdClass;

/**
 * Service for managing Item configuration and initialization parameters
 * Implements caching for better performance
 */
class ItemConfigService {

    private const CACHE_PREFIX = "item_config";
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get initialization parameters for item module
     *
     * @param int $companyId Company ID
     * @param string $page Page identifier
     * @param string $type Item type (product, service, subscription)
     * @return stdClass
     */
    public static function getInitParams(int $companyId, string $page = "", string $type = "product"): stdClass {

        $cacheKey = self::buildCacheKey($companyId, $type);

        return Cache::remember($cacheKey, self::CACHE_TTL, function() use($page, $type, $companyId) {

            $initParams = new stdClass();

            $config = new stdClass();

            if($page === "main") {

                $config->{$type."s"} = new stdClass();
                $config->{$type."s"}->statuses = Item::getStatuses();

                if($type === "subscription") {

                    $config->{$type."s"}->durationTypes = Item::getDurationTypes();

                }

                $config->categories = new stdClass();
                $config->categories->records = Category::getAll($type, $companyId);

                $config->currencies = new stdClass();
                $config->currencies->records = Currency::get();

            }

            $initParams->config = $config;
            $initParams->bool   = true;

            return $initParams;

        });

    }

    /**
     * Build cache key for item configuration
     *
     * @param int $companyId Company ID
     * @param string $type Item type
     * @return string
     */
    private static function buildCacheKey(int $companyId, string $type): string {

        return self::CACHE_PREFIX."_{$type}_company_{$companyId}";

    }

    /**
     * Clear cache for item configuration
     *
     * @param int $companyId Company ID
     * @param string $type Item type
     * @return void
     */
    public static function clearCache(int $companyId, string $type): void {

        $cacheKey = self::buildCacheKey($companyId, $type);
        Cache::forget($cacheKey);

    }

    /**
     * Clear all item configuration cache for a company
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearAllCache(int $companyId): void {

        foreach(["product", "service", "subscription"] as $type) {

            self::clearCache($companyId, $type);

        }

    }

}

