<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Companies;

use Illuminate\Support\Facades\Cache;
use App\Models\System\Organizations\Company;

/**
 * Service for managing company sections with caching
 */
class CompanySectionService {

    private const CACHE_TTL = 30; // minutes
    private const CACHE_PREFIX = "active_sections";

    /**
     * Get active sections for company with caching
     *
     * @param int $companyId Company ID
     * @param bool $forceRefresh Force cache refresh
     * @return mixed
     */
    public static function getSections(int $companyId, bool $forceRefresh = false) {

        $cacheKey = self::CACHE_PREFIX . "_{$companyId}";

        if($forceRefresh || !Cache::has($cacheKey)) {

            Cache::put("last_{$cacheKey}", now(), now()->addMinutes(self::CACHE_TTL));

            $sections = Company::getActiveSections($companyId);

            Cache::put($cacheKey, $sections, now()->addMinutes(self::CACHE_TTL));

        }else {

            Cache::put("has_{$cacheKey}", now(), now()->addMinutes(self::CACHE_TTL));

        }

        return Cache::get($cacheKey);

    }

    /**
     * Clear sections cache for company
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearCache(int $companyId): void {

        $cacheKey = self::CACHE_PREFIX . "_{$companyId}";
        Cache::forget($cacheKey);
        Cache::forget("last_{$cacheKey}");
        Cache::forget("has_{$cacheKey}");

    }

}

