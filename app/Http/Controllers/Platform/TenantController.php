<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\{Controller};
use App\Models\System\Tenancy\{TenantAnnouncement, TenantDatabase};
use App\Services\System\Tenancy\{PlatformTenantProvisioner, PlatformTenantService, TenantAdministrationService};
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Validation\{Rule, ValidationException};
use RuntimeException;

final class TenantController extends Controller {
    public function index(Request $request, TenantAdministrationService $administration): JsonResponse {

        $data = $request->validate([
            "search" => ["nullable", "string", "max:100"],
            "status" => ["nullable", Rule::in(["provisioning", "active", "inactive", "suspended"])],
            "page" => ["nullable", "integer", "min:1"],
            "per_page" => ["nullable", "integer", "min:10", "max:50"],
        ]);
        $tenants = $administration->paginate(
            trim((string) ($data["search"] ?? "")),
            $data["status"] ?? null,
            (int) ($data["per_page"] ?? 20)
        );

        return response()->json([
            "data" => collect($tenants->items())->map(fn(TenantDatabase $tenant) => $administration->serialize($tenant))->values(),
            "meta" => [
                "current_page" => $tenants->currentPage(),
                "last_page" => $tenants->lastPage(),
                "per_page" => $tenants->perPage(),
                "total" => $tenants->total(),
            ],
            "counts" => $administration->counts(),
        ]);

    }

    public function store(
        Request $request,
        PlatformTenantProvisioner $provisioner,
        TenantAdministrationService $administration
    ): JsonResponse {

        $data = $request->validate([
            "slug" => ["required", "alpha_dash", "max:60"],
            "commercial_name" => ["required", "string", "max:180"],
            "legal_name" => ["required", "string", "max:220"],
            "document_number" => ["required", "string", "max:20"],
            "admin_name" => ["required", "string", "max:150"],
            "admin_email" => ["required", "email", "max:190"],
            "admin_password" => ["required", "string", "min:10", "max:255", "confirmed"],
        ]);
        try {

            $tenant = $provisioner->create($data);

        } catch(RuntimeException $exception) {

            throw ValidationException::withMessages([
                "tenant" => $exception->getMessage(),
            ]);

        }

        return response()->json([
            "message" => "Cliente tenant creado correctamente.",
            "data" => $administration->serialize($tenant),
        ], 201);

    }

    public function show(
        TenantDatabase $tenant,
        PlatformTenantService $platformTenants,
        TenantAdministrationService $administration
    ): JsonResponse {

        return response()->json([
            "data" => [
                "tenant" => $administration->serialize($tenant->load("domains")),
                "modules" => $platformTenants->modules($tenant),
                "announcements" => TenantAnnouncement::query()
                    ->where("tenant_database_id", $tenant->id)
                    ->latest()
                    ->limit(50)
                    ->get(),
            ],
        ]);

    }

    public function status(
        Request $request,
        TenantDatabase $tenant,
        TenantAdministrationService $administration
    ): JsonResponse {

        $data = $request->validate([
            "status" => ["required", Rule::in(["active", "inactive", "suspended"])],
        ]);
        $actor = $request->attributes->get("platformUser");
        $updated = $administration->changeStatus($tenant->slug, $data["status"], $actor?->email);

        return response()->json([
            "message" => "Estado del cliente actualizado.",
            "data" => $administration->serialize($updated),
        ]);

    }

    public function modules(
        Request $request,
        TenantDatabase $tenant,
        PlatformTenantService $platformTenants,
        TenantAdministrationService $administration
    ): JsonResponse {

        $data = $request->validate(["modules" => ["nullable", "array"], "modules.*" => ["integer"]]);
        $enabledCount = $platformTenants->updateModules($tenant, $data["modules"] ?? []);
        $actor = $request->attributes->get("platformUser");
        $administration->audit($tenant, "modules_updated", "success", ["enabled_count" => $enabledCount], $actor?->email);

        return response()->json([
            "message" => "Módulos actualizados: {$enabledCount} activos.",
            "data" => ["enabled_count" => $enabledCount],
        ]);

    }

    public function announcement(Request $request, TenantDatabase $tenant): JsonResponse {

        $data = $request->validate([
            "title" => ["required", "string", "max:180"],
            "message" => ["required", "string", "max:2000"],
            "severity" => ["required", Rule::in(["info", "success", "warning", "danger"])],
            "starts_at" => ["nullable", "date"],
            "ends_at" => ["nullable", "date", "after_or_equal:starts_at"],
            "dismissible" => ["nullable", "boolean"],
        ]);
        $user = $request->attributes->get("platformUser");
        $announcement = TenantAnnouncement::query()->create($data + [
            "tenant_database_id" => $tenant->id,
            "dismissible" => (bool) ($data["dismissible"] ?? false),
            "status" => "active",
            "created_by" => $user->id,
            "updated_by" => $user->id,
        ]);

        return response()->json([
            "message" => "Aviso publicado en el tenant.",
            "data" => $announcement,
        ], 201);

    }

    public function announcementStatus(
        Request $request,
        TenantDatabase $tenant,
        TenantAnnouncement $announcement
    ): JsonResponse {

        abort_unless((int) $announcement->tenant_database_id === (int) $tenant->id, 404);
        $data = $request->validate(["status" => ["required", Rule::in(["active", "inactive"])]]);
        $user = $request->attributes->get("platformUser");
        $announcement->forceFill($data + ["updated_by" => $user->id])->save();

        return response()->json([
            "message" => "Estado del aviso actualizado.",
            "data" => $announcement->fresh(),
        ]);

    }
}
