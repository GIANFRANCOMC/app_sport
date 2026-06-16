<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Companies;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

use App\Models\System\General\Section;
use App\Services\System\Organizations\Roles\RolePermissionService;

/**
 * Resolves and caches the modules enabled for a company.
 */
final class CompanySectionService {

    private const CACHE_TTL = 1800;
    private const CACHE_PREFIX = "company_sections";

    public static function getSections(int $companyId, ?int $roleId = null, bool $forceRefresh = false): Collection {

        self::validateCompanyId($companyId);

        $cacheKey = self::cacheKey($companyId, $roleId);

        if($forceRefresh) {

            Cache::forget($cacheKey);

        }

        return Cache::remember(
            $cacheKey,
            self::CACHE_TTL,
            fn() => self::querySections($companyId, $roleId)
        );

    }

    public static function clearCache(int $companyId, ?int $roleId = null): void {

        self::validateCompanyId($companyId);

        Cache::forget(self::cacheKey($companyId, $roleId));

    }

    public static function clearCompanyCache(int $companyId): void {

        self::clearCache($companyId);

        \App\Models\System\Organizations\Role::query()
            ->where("company_id", $companyId)
            ->pluck("id")
            ->each(fn($roleId) => self::clearCache($companyId, (int) $roleId));

    }

    public static function cacheKey(int $companyId, ?int $roleId = null): string {

        self::validateCompanyId($companyId);

        return self::CACHE_PREFIX.":company:{$companyId}:role:".($roleId ?: "all");

    }

    private static function querySections(int $companyId, ?int $roleId = null): Collection {

        $mustFilterByRole = $roleId && !RolePermissionService::isFullAccess($companyId, $roleId);
        $allowedSubSectionIds = $mustFilterByRole
            ? RolePermissionService::allowedSubSectionIds($companyId, $roleId)
            : [];

        $query = Section::query()
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

                      });

        if($mustFilterByRole) {

            $query->whereHas("subSections", function($subQuery) use($allowedSubSectionIds) {

                $subQuery->whereIn("id", $allowedSubSectionIds);

            });

        }

        return $query->with(["subSections" => function($query) use($companyId, $allowedSubSectionIds, $mustFilterByRole) {

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

                                });

                          if($mustFilterByRole) {

                              $query->whereIn("id", $allowedSubSectionIds);

                          }

                          $query
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
