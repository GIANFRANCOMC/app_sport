<?php

declare(strict_types=1);

namespace App\Services\System\Organizations;

use App\Models\System\General\IdentityDocumentType;
use App\Models\System\Organizations\BookComplaint;
use Illuminate\Support\Facades\{Auth, Cache};
use stdClass;

/**
 * Service for managing BookComplaint configuration and initialization parameters
 * Implements caching for better performance
 */
class BookComplaintConfigService {

    private const CACHE_PREFIX = "book_complaint_config";
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Get initialization parameters for book complaint module
     *
     * @param int $companyId Company ID
     * @param string $page Page identifier (only used to determine what data to return, not for cache key)
     * @return stdClass
     */
    public static function getInitParams(int $companyId, string $page = ""): stdClass {

        $cacheKey = self::buildCacheKey($companyId);

        return Cache::remember($cacheKey, self::CACHE_TTL, function() use($companyId, $page) {

            $initParams = new stdClass();

            $config = new stdClass();

            if($page === "main") {

                $userAuth = Auth::user();

                $config->identityDocumentTypes = new stdClass();
                $config->identityDocumentTypes->records = IdentityDocumentType::getAll("book_complaint", $companyId);

                $config->bookComplaints = new stdClass();
                $config->bookComplaints->types    = BookComplaint::getTypes();
                $config->bookComplaints->statuses = BookComplaint::getStatuses();

            }

            $initParams->config = $config;
            $initParams->bool   = true;

            return $initParams;

        });

    }

    /**
     * Build cache key for book complaint configuration
     *
     * @param int $companyId Company ID
     * @return string
     */
    private static function buildCacheKey(int $companyId): string {

        return self::CACHE_PREFIX."_company_{$companyId}";

    }

    /**
     * Clear cache for book complaint configuration
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearCache(int $companyId): void {

        $cacheKey = self::buildCacheKey($companyId);
        Cache::forget($cacheKey);

    }

    /**
     * Clear all book complaint configuration cache for a company
     *
     * @param int $companyId Company ID
     * @return void
     */
    public static function clearAllCache(int $companyId): void {

        self::clearCache($companyId);

    }

}

