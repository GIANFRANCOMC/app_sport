<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Roles;

use App\Models\System\Organizations\{Role, User};
use Illuminate\Support\Facades\Cache;

final class RolePermissionService {

    private const CACHE_TTL = 1800;
    private const CACHE_PREFIX = "role_permissions";

    private const PUBLIC_ROUTE_PREFIXES = [
        "home",
        "helpers",
        "logout"
    ];

    public static function canAccessRoute(User $user, ?string $routeName): bool {

        if(!$routeName) return true;

        $prefix = self::routePrefix($routeName);

        if(in_array($prefix, self::PUBLIC_ROUTE_PREFIXES, true)) {

            return true;

        }

        if(!$user->role_id) {

            return false;

        }

        $permissions = self::getPermissions((int) $user->company_id, (int) $user->role_id);

        if($permissions["is_full_access"]) {

            return true;

        }

        return in_array($prefix, $permissions["route_prefixes"], true);

    }

    public static function allowedSubSectionIds(int $companyId, int $roleId): array {

        $permissions = self::getPermissions($companyId, $roleId);

        if($permissions["is_full_access"]) {

            return [];

        }

        return $permissions["sub_section_ids"];

    }

    public static function isFullAccess(int $companyId, int $roleId): bool {

        return self::getPermissions($companyId, $roleId)["is_full_access"];

    }

    public static function clearRoleCache(int $companyId, int $roleId): void {

        Cache::forget(self::cacheKey($companyId, $roleId));

    }

    public static function clearCompanyCache(int $companyId): void {

        Role::query()
            ->where("company_id", $companyId)
            ->pluck("id")
            ->each(fn($roleId) => self::clearRoleCache($companyId, (int) $roleId));

    }

    public static function cacheKey(int $companyId, int $roleId): string {

        return self::CACHE_PREFIX.":company:{$companyId}:role:{$roleId}";

    }

    private static function getPermissions(int $companyId, int $roleId): array {

        return Cache::remember(
            self::cacheKey($companyId, $roleId),
            self::CACHE_TTL,
            fn() => self::queryPermissions($companyId, $roleId)
        );

    }

    private static function queryPermissions(int $companyId, int $roleId): array {

        $role = Role::query()
            ->where("company_id", $companyId)
            ->where("status", "active")
            ->with(["subSections" => function($query) use($companyId) {

                $query->select([
                    "sub_sections.id",
                    "sub_sections.dom_route"
                ])->whereHas("companiesSubSections", function($companyQuery) use($companyId) {

                    $companyQuery->where("company_id", $companyId);

                });

            }])
            ->find($roleId);

        if(!$role) {

            return [
                "is_full_access" => false,
                "sub_section_ids" => [],
                "route_prefixes" => []
            ];

        }

        $subSections = $role->subSections;

        return [
            "is_full_access" => (bool) $role->is_full_access,
            "sub_section_ids" => $subSections->pluck("id")->map(fn($id) => (int) $id)->values()->all(),
            "route_prefixes" => $subSections
                ->pluck("dom_route")
                ->filter()
                ->map(fn($route) => self::routePrefix((string) $route))
                ->unique()
                ->values()
                ->all()
        ];

    }

    private static function routePrefix(string $routeName): string {

        return explode(".", $routeName)[0] ?? $routeName;

    }

}
