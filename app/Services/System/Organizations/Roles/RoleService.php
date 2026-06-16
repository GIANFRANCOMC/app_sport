<?php

declare(strict_types=1);

namespace App\Services\System\Organizations\Roles;

use App\Helpers\System\Utilities;
use App\Models\System\Organizations\{Role, RoleSubSection};
use App\Services\System\Organizations\Companies\CompanySectionService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class RoleService {

    public static function query(int $companyId, string $word = ""): Builder {

        $query = Role::query()
            ->where("company_id", $companyId)
            ->with("roleSubSections:id,role_id,sub_section_id")
            ->withCount([
                "users",
                "roleSubSections as modules_count"
            ]);

        $word = trim($word);

        if($word !== "") {

            $query->where("name", "like", "%{$word}%");

        }

        return $query->orderByDesc("is_full_access")->orderBy("name");

    }

    public static function find(int $companyId, int $roleId): Role {

        return Role::query()
            ->where("company_id", $companyId)
            ->with("roleSubSections:id,role_id,sub_section_id")
            ->findOrFail($roleId);

    }

    public static function create(int $companyId, int $userId, array $data): Role {

        return DB::transaction(function() use($companyId, $userId, $data) {

            $role = Role::create([
                "company_id" => $companyId,
                "slug" => Utilities::generateCode(),
                "name" => trim((string) $data["name"]),
                "is_full_access" => (bool) $data["is_full_access"],
                "status" => $data["status"],
                "created_at" => now(),
                "created_by" => $userId
            ]);

            self::syncPermissions($companyId, $role, $data["sub_section_ids"] ?? [], $userId);

            return self::find($companyId, $role->id);

        });

    }

    public static function update(int $companyId, int $roleId, int $userId, array $data): Role {

        return DB::transaction(function() use($companyId, $roleId, $userId, $data) {

            $role = Role::query()
                ->where("company_id", $companyId)
                ->findOrFail($roleId);
            $role->update([
                "name" => trim((string) $data["name"]),
                "is_full_access" => (bool) $data["is_full_access"],
                "status" => $data["status"],
                "updated_at" => now(),
                "updated_by" => $userId
            ]);

            self::syncPermissions($companyId, $role, $data["sub_section_ids"] ?? [], $userId);

            return self::find($companyId, $role->id);

        });

    }

    private static function syncPermissions(
        int $companyId,
        Role $role,
        array $subSectionIds,
        int $userId
    ): void {

        $enabledIds = self::enabledSubSectionIds($companyId);
        $selectedIds = collect($subSectionIds)
            ->map(fn($id) => (int) $id)
            ->intersect($enabledIds)
            ->unique()
            ->values();

        RoleSubSection::query()
            ->where("role_id", $role->id)
            ->delete();

        if($role->is_full_access || $selectedIds->isEmpty()) {

            RolePermissionService::clearRoleCache($companyId, (int) $role->id);
            \App\Services\System\Organizations\Companies\CompanySectionService::clearCache($companyId, (int) $role->id);
            return;

        }

        RoleSubSection::insert($selectedIds->map(fn($subSectionId) => [
            "role_id" => $role->id,
            "sub_section_id" => $subSectionId,
            "status" => "active",
            "created_at" => now(),
            "created_by" => $userId
        ])->all());

        RolePermissionService::clearRoleCache($companyId, (int) $role->id);
        \App\Services\System\Organizations\Companies\CompanySectionService::clearCache($companyId, (int) $role->id);

    }

    private static function enabledSubSectionIds(int $companyId) {

        return CompanySectionService::getSections($companyId)
            ->pluck("subSections")
            ->flatten()
            ->pluck("id")
            ->map(fn($id) => (int) $id);

    }

}
