<?php

declare(strict_types=1);

namespace App\Services\System\Customers;

use App\Models\System\Customers\Customer;
use App\Models\System\General\IdentityDocumentType;
use App\Services\System\Devices\Biometric\BiometricDeviceService;
use Illuminate\Support\Facades\Cache;
use stdClass;

/**
 * Service for managing Customer configuration and initialization parameters
 * Implements caching for better performance
 */
class CustomerConfigService {

    private const CACHE_PREFIX = "customer_config";
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get initialization parameters for customer module
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
                $config->identityDocumentTypes->records = IdentityDocumentType::getAll("customer", $companyId);

                $config->customers = new stdClass();
                $config->customers->genders  = Customer::getGenders();
                $config->customers->statuses = Customer::getStatuses();

                $config->biometricDevices = new stdClass();
                $config->biometricDevices->records = BiometricDeviceService::getActiveDevices($companyId);

            }

            $initParams->config = $config;
            $initParams->bool   = true;

            return $initParams;

        });

    }

    /**
     * Build cache key for customer configuration
     *
     * @param int $companyId Company ID
     * @return string
     */
    private static function buildCacheKey(int $companyId): string {

        return self::CACHE_PREFIX."_company_{$companyId}";

    }

    /**
     * Clear cache for customer configuration
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearCache(int $companyId): void {

        $cacheKey = self::buildCacheKey($companyId);
        Cache::forget($cacheKey);

    }

    /**
     * Clear all customer configuration cache for a company
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearAllCache(int $companyId): void {

        self::clearCache($companyId);

    }

}

