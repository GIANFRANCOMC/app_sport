@extends('Platform.layouts.app')
@section('title', 'Acceso administrativo')
@section('content')
<div class="platform-login">
    <div class="platform-card platform-login__card">
        <div class="platform-card__body p-4">
            <div class="mb-4"><div class="platform-brand mb-2">Gympe</div><h1 class="platform-title">Administración SaaS</h1><p class="platform-subtitle">Gestiona clientes, módulos y comunicaciones.</p></div>
            <form method="POST" action="{{ route('platform.login.store') }}">@csrf
                <div class="mb-3"><label class="form-label">Correo</label><input class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus></div>
                <div class="mb-4"><label class="form-label">Contraseña</label><input class="form-control" type="password" name="password" required></div>
                <button class="btn platform-btn-primary text-white w-100">Ingresar</button>
            </form>
        </div>
    </div>
</div>
@endsection
