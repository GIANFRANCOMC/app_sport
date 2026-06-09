<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Companies;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

use App\Models\System\General\Section;

/**
 * Resolves and caches the modules enabled for a company.
 */
final class CompanySectionService {

    private const CACHE_TTL = 1800;
    private const CACHE_PREFIX = "company_sections";

    public static function getSections(int $companyId, bool $forceRefresh = false): Collection {

        self::validateCompanyId($companyId);

        $cacheKey = self::cacheKey($companyId);

        if($forceRefresh) {

            Cache::forget($cacheKey);

        }

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            fn() => self::querySections($companyId)
        );

    }

    public static function clearCache(int $companyId): void {

        self::validateCompanyId($companyId);

        Cache::forget(self::cacheKey($companyId));

    }

    public static function cacheKey(int $companyId): string {

        self::validateCompanyId($companyId);

        return self::CACHE_PREFIX.":company:{$companyId}";

    }

    private static function querySections(int $companyId): Collection {

        return Section::query()
                      ->select([
                          "id",
                          "slug",
                          "name",
                          "order",
                          "dom_id",
                          "dom_label",
                          "dom_icon",
                          "has_sub_menu",
                          "status"
                      ])
                      ->where("status", "active")
                      ->whereHas("subSections.companiesSubSections", function($query) use($companyId) {

                          $query->where("company_id", $companyId);

                      })
                      ->with(["subSections" => function($query) use($companyId) {

                          $query->select([
                                    "id",
                                    "section_id",
                                    "slug",
                                    "name",
                                    "description",
                                    "order",
                                    "dom_id",
                                    "dom_label",
                                    "dom_icon",
                                    "dom_route",
                                    "status"
                                ])
                                ->whereHas("companiesSubSections", function($companyQuery) use($companyId) {

                                    $companyQuery->where("company_id", $companyId);

                                })
                                ->orderBy("order");

                      }])
                      ->orderBy("order")
                      ->get()
                      ->each(function(Section $section) {

                          $section->setAppends([]);

                          $section->subSections->each(function($subSection) {

                              $subSection->setAppends([]);
                              $subSection->dom_route_url = route($subSection->dom_route);

                          });

                      });

    }

    private static function validateCompanyId(int $companyId): void {

        if($companyId <= 0) {

            throw new InvalidArgumentException("Company ID must be greater than zero.");

        }

    }

}
