<?php

declare(strict_types=1);

namespace App\Services\System\Sales;

use App\Models\System\Catalogs\Item;
use App\Models\System\Customers\Customer;
use App\Models\System\General\{Currency, IdentityDocumentType};
use App\Models\System\Organizations\Branch;
use App\Models\System\Sales\SaleHeader;
use Illuminate\Support\Facades\Cache;
use stdClass;

/**
 * Service for managing Sale configuration and initialization parameters
 * Implements caching for better performance
 */
class SaleConfigService {

    private const CACHE_PREFIX = "sale_config";
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get initialization parameters for sale module
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

            if($page === "list") {

                $config->branches = new stdClass();
                $config->branches->records = Branch::getAll("default", $companyId);

                $config->customers = new stdClass();
                $config->customers->records = Customer::getAll("default", $companyId);

                $config->salesHeader = new stdClass();
                $config->salesHeader->statuses = SaleHeader::getStatuses();

            }elseif($page === "main") {

                $config->branches = new stdClass();
                $config->branches->records = Branch::getAll("sale", $companyId);

                $config->currencies = new stdClass();
                $config->currencies->records = Currency::get();

                $config->customers = new stdClass();
                $config->customers->records  = Customer::getAll("sale", $companyId);
                $config->customers->identityDocumentTypes = IdentityDocumentType::getAll("customer", $companyId);
                $config->customers->genders  = Customer::getGenders();
                $config->customers->statuses = Customer::getStatuses();

                $config->items = new stdClass();
                $config->items->durationTypes = Item::getDurationTypes();
                $config->items->records = Item::getAll("sale", $companyId);

                $config->salesHeader = new stdClass();
                $config->salesHeader->statuses = SaleHeader::getStatuses();

            }

            $initParams->config = $config;
            $initParams->bool   = true;

            return $initParams;

        });

    }

    /**
     * Build cache key for sale configuration
     *
     * @param int $companyId Company ID
     * @param string $page Page identifier
     * @return string
     */
    private static function buildCacheKey(int $companyId, string $page): string {

        return self::CACHE_PREFIX."_company_{$companyId}_page_{$page}";

    }

    /**
     * Clear cache for sale configuration
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

            // Clear all pages
            Cache::forget(self::buildCacheKey($companyId, "list"));
            Cache::forget(self::buildCacheKey($companyId, "main"));

        }

    }

    /**
     * Clear all sale configuration cache for a company
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearAllCache(int $companyId): void {

        self::clearCache($companyId);

    }

}

