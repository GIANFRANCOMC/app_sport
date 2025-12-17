<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Companies;

use App\Models\System\Organizations\{Company};
use App\Models\System\General\IdentityDocumentType;
use Illuminate\Support\Facades\Cache;
use stdClass;

/**
 * Service for managing Company configuration and initialization parameters
 * Implements caching for better performance
 */
class CompanyConfigService {

    private const CACHE_PREFIX = "company_config";
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get initialization parameters for company module
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

                $config->companies = new stdClass();
                $config->companies->statuses = Company::getStatuses();

                $company = Company::where("id", $companyId)->first();

                if($company) {

                    $socialsMedia = $company->socialsMedia;

                    // Attach social media links to company object
                    $company->facebook  = optional($socialsMedia->where("type", "facebook")->first())->link;
                    $company->instagram = optional($socialsMedia->where("type", "instagram")->first())->link;
                    $company->whatsapp  = optional($socialsMedia->where("type", "whatsapp")->first())->link;

                    $config->company = new stdClass();
                    $config->company->records = [$company];

                }

                $config->identityDocumentTypes = new stdClass();
                $config->identityDocumentTypes->records = IdentityDocumentType::getAll("company");

            }

            $initParams->config = $config;
            $initParams->bool   = true;

            return $initParams;

        });

    }

    /**
     * Build cache key for company configuration
     *
     * @param int $companyId Company ID
     * @return string
     */
    private static function buildCacheKey(int $companyId): string {

        return self::CACHE_PREFIX."_company_{$companyId}";

    }

    /**
     * Clear cache for company configuration
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearCache(int $companyId): void {

        $cacheKey = self::buildCacheKey($companyId);
        Cache::forget($cacheKey);

    }

    /**
     * Clear all company configuration cache for a company
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearAllCache(int $companyId): void {

        self::clearCache($companyId);

    }

}

