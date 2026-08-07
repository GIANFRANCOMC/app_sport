@extends('Platform.layouts.app')
@section('title', 'Clientes tenant')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div><h1 class="platform-title">Clientes tenant</h1><p class="platform-subtitle">Control central de acceso, módulos y avisos.</p></div>
    <button class="btn platform-btn-primary text-white" data-bs-toggle="collapse" data-bs-target="#newTenant"><i class="fa-solid fa-plus me-1"></i> Nuevo cliente</button>
</div>
<div class="row g-3 mb-4">
    @foreach(['Todos' => $tenants->count(), 'Activos' => ($counts['active'] ?? 0), 'Inactivos' => ($counts['inactive'] ?? 0), 'Suspendidos' => ($counts['suspended'] ?? 0)] as $label => $value)
        <div class="col-6 col-lg-3"><div class="platform-card platform-kpi"><div class="platform-kpi__value">{{ $value }}</div><div class="platform-kpi__label">{{ $label }}</div></div></div>
    @endforeach
</div>
<div class="collapse mb-4" id="newTenant"><div class="platform-card"><div class="platform-card__head"><strong>Crear cliente tenant</strong></div><div class="platform-card__body">
    <form method="POST" action="{{ route('platform.tenants.store') }}">@csrf
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Subdominio</label><div class="input-group"><input class="form-control" name="slug" required><span class="input-group-text">.{{ config('tenancy.base_domain') }}</span></div></div>
            <div class="col-md-3"><label class="form-label">Nombre comercial</label><input class="form-control" name="commercial_name" required></div>
            <div class="col-md-3"><label class="form-label">Razón social</label><input class="form-control" name="legal_name" required></div>
            <div class="col-md-3"><label class="form-label">Documento</label><input class="form-control" name="document_number" required></div>
            <div class="col-md-4"><label class="form-label">Administrador</label><input class="form-control" name="admin_name" required></div>
            <div class="col-md-4"><label class="form-label">Correo administrador</label><input class="form-control" type="email" name="admin_email" required></div>
            <div class="col-md-4"><label class="form-label">Contraseña inicial</label><input class="form-control" type="password" name="admin_password" minlength="8" required></div>
        </div>
        <div class="text-end mt-3"><button class="btn platform-btn-primary text-white">Crear y aprovisionar</button></div>
    </form>
</div></div></div>
<div class="platform-card"><div class="table-responsive"><table class="table platform-table align-middle mb-0">
    <thead><tr><th>Cliente</th><th>Dominio</th><th>Base de datos</th><th>Estado</th><th class="text-end">Administración</th></tr></thead>
    <tbody>@forelse($tenants as $tenant)<tr>
        <td><strong>{{ $tenant->slug }}</strong><div class="small text-muted">Empresa #{{ $tenant->company_id }}</div></td>
        <td>{{ $tenant->domains->firstWhere('is_primary', true)?->domain ?? 'Sin dominio' }}</td><td><code>{{ $tenant->database_name }}</code></td>
        <td><span class="platform-status platform-status--{{ $tenant->status }}">{{ ucfirst($tenant->status) }}</span></td>
        <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('platform.tenants.show', $tenant) }}">Administrar</a></td>
    </tr>@empty<tr><td colspan="5" class="text-center text-muted py-5">Aún no existen clientes tenant.</td></tr>@endforelse</tbody>
</table></div></div>
@endsection
