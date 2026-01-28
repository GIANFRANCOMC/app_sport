<?php

declare(strict_types=1);

namespace App\Services\System\Catalogs\Products;

/**
 * Service for managing Product configuration
 */
class ProductConfigService {

    /**
     * Get initialization parameters for product module
     *
     * @param int $companyId Company ID
     * @param string $page Page identifier
     * @return \stdClass
     */
    public static function getInitParams(int $companyId, string $page = ""): \stdClass {

        return ItemConfigService::getInitParams($companyId, $page, "product");

    }

    /**
     * Clear cache for product configuration
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearCache(int $companyId): void {

        ItemConfigService::clearCache($companyId, "product");

    }

    /**
     * Clear all product configuration cache
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearAllCache(int $companyId): void {

        self::clearCache($companyId);

    }

}

