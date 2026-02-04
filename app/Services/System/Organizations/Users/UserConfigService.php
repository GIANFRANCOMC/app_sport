<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Users;

use Illuminate\Support\Facades\Cache;
use stdClass;

use App\Models\System\General\{IdentityDocumentType};
use App\Models\System\Organizations\{Role, User};

/**
 * Service for managing User configuration and initialization parameters
 * Implements caching for better performance
 */
class UserConfigService {

    private const CACHE_PREFIX = "user_config";
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

                $config->identityDocumentTypes = new stdClass();
                $config->identityDocumentTypes->records = IdentityDocumentType::getAll("default", $companyId);

                $config->roles = new stdClass();
                $config->roles->records = Role::getAll("default", $companyId);

                $config->genders  = User::getGenders();
                $config->statuses = User::getStatuses();

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
