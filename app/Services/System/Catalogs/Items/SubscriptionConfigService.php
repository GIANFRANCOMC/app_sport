<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Items;

/**
 * Service for managing Subscription configuration
 */
class SubscriptionConfigService {

    /**
     * Get initialization parameters for subscription module
     *
     * @param int $companyId Company ID
     * @param string $page Page identifier
     * @return \stdClass
     */
    public static function getInitParams(int $companyId, string $page = ""): \stdClass {

        return ItemConfigService::getInitParams($companyId, $page, "subscription");

    }

    /**
     * Clear cache for subscription configuration
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearCache(int $companyId): void {

        ItemConfigService::clearCache($companyId, "subscription");

    }

    /**
     * Clear all subscription configuration cache
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearAllCache(int $companyId): void {

        self::clearCache($companyId);

    }

}

