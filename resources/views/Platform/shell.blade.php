@extends('Platform.layouts.app')
@section('title', 'Administración SaaS')
@section('content')
<div id="platform-app"></div>
<script>
    window.PlatformConfig = {{ Illuminate\Support\Js::from([
        'user' => ['name' => $platformUser->name, 'email' => $platformUser->email],
        'initialTenantId' => $initialTenantId,
        'routes' => [
            'tenants' => route('platform.tenants.index'),
            'tenantApi' => url('/api/tenants'),
            'logout' => route('platform.logout'),
            'login' => route('platform.login'),
        ],
    ]) }};
</script>
@endsection
@push('scripts')
@vite('resources/js/Platform/app.js')
@endpush
