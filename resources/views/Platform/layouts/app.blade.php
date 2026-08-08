<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administración SaaS')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('System/assets/vendor/css/rtl/core.css') }}">
    <link rel="stylesheet" href="{{ asset('System/assets/vendor/fonts/fontawesome.css') }}">
    @vite('resources/css/Platform/app.css')
</head>
<body class="platform-body">
<div class="platform-shell">
    @if(isset($platformUser))
        <nav class="platform-nav">
            <div class="platform-nav__inner">
                <div class="platform-nav__left">
                    <a class="platform-brand" href="{{ route('platform.tenants.index') }}">Gympe <small>Administración SaaS</small></a>
                    <a class="platform-nav__link {{ request()->routeIs('platform.tenants.*') ? 'is-active' : '' }}" href="{{ route('platform.tenants.index') }}">Tenants</a>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="platform-secure"><i class="fa-solid fa-shield-halved"></i> Landlord</span>
                    <span class="small text-muted">{{ $platformUser->name }}</span>
                    <form method="POST" action="{{ route('platform.logout') }}">@csrf<button class="btn btn-sm btn-outline-secondary">Salir</button></form>
                </div>
            </div>
        </nav>
    @endif
    <main class="{{ isset($platformUser) ? 'platform-main' : '' }}">
        @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="alert alert-danger"><strong>Revisa la información.</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        @yield('content')
    </main>
</div>
<script src="{{ asset('System/assets/vendor/js/bootstrap.js') }}"></script>
</body>
</html>
