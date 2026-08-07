<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\System\Tenancy\{TenantAnnouncement, TenantDatabase};
use App\Services\System\Tenancy\{PlatformTenantService, TenantAdministrationService, TenantConnectionManager};
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

final class TenantController extends Controller
{
    public function index(TenantAdministrationService $administration): View
    {
        $tenants = $administration->list();

        return view('Platform.tenants.index', [
            'tenants' => $tenants,
            'counts' => $tenants->countBy('status'),
        ]);
    }

    public function store(Request $request, TenantConnectionManager $connections): RedirectResponse
    {
        $data = $request->validate([
            'slug' => ['required', 'alpha_dash', 'max:60'],
            'commercial_name' => ['required', 'string', 'max:180'],
            'legal_name' => ['required', 'string', 'max:220'],
            'document_number' => ['required', 'string', 'max:20'],
            'admin_name' => ['required', 'string', 'max:150'],
            'admin_email' => ['required', 'email', 'max:190'],
            'admin_password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        try {
            $exitCode = Artisan::call('tenant:create', [
                'slug' => strtolower($data['slug']),
                '--commercial-name' => $data['commercial_name'],
                '--legal-name' => $data['legal_name'],
                '--document-number' => $data['document_number'],
                '--admin-name' => $data['admin_name'],
                '--admin-email' => $data['admin_email'],
                '--admin-password' => $data['admin_password'],
                '--skip-cache-clear' => true,
            ]);
        } finally {
            $connections->disconnect();
        }

        if($exitCode !== 0) {
            throw new RuntimeException(trim(Artisan::output()) ?: 'No se pudo crear el tenant.');
        }

        return redirect()->route('platform.tenants.index')->with('success', 'Cliente tenant creado correctamente.');
    }

    public function show(TenantDatabase $tenant, PlatformTenantService $platformTenants): View
    {
        return view('Platform.tenants.show', [
            'tenant' => $tenant->load('domains'),
            'modules' => $platformTenants->modules($tenant),
            'announcements' => TenantAnnouncement::query()
                ->where('tenant_database_id', $tenant->id)
                ->latest()
                ->get(),
        ]);
    }

    public function status(
        Request $request,
        TenantDatabase $tenant,
        TenantAdministrationService $administration
    ): RedirectResponse {
        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive', 'suspended'])],
        ]);
        $actor = $request->attributes->get('platformUser');
        $administration->changeStatus($tenant->slug, $data['status'], $actor?->email);

        return back()->with('success', 'Estado del cliente actualizado.');
    }

    public function modules(
        Request $request,
        TenantDatabase $tenant,
        PlatformTenantService $platformTenants,
        TenantAdministrationService $administration
    ): RedirectResponse {
        $data = $request->validate(['modules' => ['nullable', 'array'], 'modules.*' => ['integer']]);
        $enabledCount = $platformTenants->updateModules($tenant, $data['modules'] ?? []);
        $actor = $request->attributes->get('platformUser');
        $administration->audit($tenant, 'modules_updated', 'success', ['enabled_count' => $enabledCount], $actor?->email);

        return back()->with('success', "Módulos actualizados: {$enabledCount} activos.");
    }

    public function announcement(Request $request, TenantDatabase $tenant): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:2000'],
            'severity' => ['required', Rule::in(['info', 'success', 'warning', 'danger'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'dismissible' => ['nullable', 'boolean'],
        ]);
        $user = $request->attributes->get('platformUser');

        TenantAnnouncement::query()->create($data + [
            'tenant_database_id' => $tenant->id,
            'dismissible' => (bool) ($data['dismissible'] ?? false),
            'status' => 'active',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return back()->with('success', 'Aviso publicado en el tenant.');
    }

    public function announcementStatus(
        Request $request,
        TenantDatabase $tenant,
        TenantAnnouncement $announcement
    ): RedirectResponse {
        abort_unless((int) $announcement->tenant_database_id === (int) $tenant->id, 404);
        $data = $request->validate(['status' => ['required', Rule::in(['active', 'inactive'])]]);
        $user = $request->attributes->get('platformUser');
        $announcement->forceFill($data + ['updated_by' => $user->id])->save();

        return back()->with('success', 'Estado del aviso actualizado.');
    }
}
