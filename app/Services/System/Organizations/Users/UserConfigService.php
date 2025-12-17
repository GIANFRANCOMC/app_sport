<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Users;

use App\Models\System\Organizations\{Role, User};
use App\Models\System\General\IdentityDocumentType;
use Illuminate\Support\Facades\Cache;
use stdClass;

/**
 * Service for managing User configuration and initialization parameters
 * Implements caching for better performance
 */
class UserConfigService {

    private const CACHE_PREFIX = "user_config";
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get initialization parameters for user module
     *
     * @param int $companyId Company ID
     * @param string $page Page identifier
     * @return stdClass
     */
    public static function getInitParams(int $companyId, string $page = ""): stdClass {

        $cacheKey = self::buildCacheKey($companyId);

        return Cache::remember($cacheKey, self::CACHE_TTL, function() use($page, $companyId) {

            $initParams = new stdClass();

            $config = new stdClass();

            if($page === "main") {

                $config->identityDocumentTypes = new stdClass();
                $config->identityDocumentTypes->records = IdentityDocumentType::getAll("user", $companyId);

                $config->roles = new stdClass();
                $config->roles->records = Role::getAll("user", $companyId);

                $config->users = new stdClass();
                $config->users->genders  = User::getGenders();
                $config->users->statuses = User::getStatuses();

            }

            $initParams->config = $config;
            $initParams->bool   = true;

            return $initParams;

        });

    }

    /**
     * Build cache key for user configuration
     *
     * @param int $companyId Company ID
     * @return string
     */
    private static function buildCacheKey(int $companyId): string {

        return self::CACHE_PREFIX."_company_{$companyId}";

    }

    /**
     * Clear cache for user configuration
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearCache(int $companyId): void {

        $cacheKey = self::buildCacheKey($companyId);
        Cache::forget($cacheKey);

    }

    /**
     * Clear all user configuration cache for a company
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearAllCache(int $companyId): void {

        self::clearCache($companyId);

    }

}

