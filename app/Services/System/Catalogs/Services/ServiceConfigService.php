<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Services;

/**
 * Service for managing Service configuration
 */
class ServiceConfigService {

    /**
     * Get initialization parameters for service module
     *
     * @param int $companyId Company ID
     * @param string $page Page identifier
     * @return \stdClass
     */
    public static function getInitParams(int $companyId, string $page = ""): \stdClass {

        return ItemConfigService::getInitParams($companyId, $page, "service");

    }

    /**
     * Clear cache for service configuration
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearCache(int $companyId): void {

        ItemConfigService::clearCache($companyId, "service");

    }

    /**
     * Clear all service configuration cache
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearAllCache(int $companyId): void {

        self::clearCache($companyId);

    }

}

