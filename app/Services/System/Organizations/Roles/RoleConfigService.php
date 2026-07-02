<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Roles;

use App\Services\System\Base\BaseConfigService;
use App\Services\System\Base\CompanyReferenceDataService;
use App\Services\System\Organizations\Companies\CompanySectionService;
use App\Models\System\Organizations\Role;
use Illuminate\Support\Facades\Auth;
use stdClass;

final class RoleConfigService extends BaseConfigService {

    protected static function getCachePrefix(): string {

        return "roles";

    }

    protected static function usesUserScopedCache(): bool {

        return true;

    }

    protected static function buildConfig(int $companyId, string $page): stdClass {

        $user = Auth::user();
        $sections = CompanySectionService::getSections($companyId, $user?->role_id);
        $delegableActions = $user ? RolePermissionService::allowedActionsBySubSection($user) : [];
        $references = CompanyReferenceDataService::for($companyId);

        $sections->each(function($section) use($delegableActions): void {
            $section->subSections->each(function($subSection) use($delegableActions): void {
                $subSection->setAttribute(
                    "delegable_actions",
                    $delegableActions[(int) $subSection->id] ?? null
                );
            });
        });

        return self::data([
            "sections" => self::data([
                "records" => $sections
            ]),
            "permissionActions" => config("permissions.actions", []),
            "branches" => $references->activeBranches(),
            "cashRegisters" => $references->cashRegisters(),
            "warehouses" => $references->stockWarehouses(),
            "statuses" => Role::getStatuses()
        ]);

    }

}
