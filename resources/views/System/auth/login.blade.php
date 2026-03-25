<x-system-guest-layout>
    @php
        $ownerApp     = config("app.owner_app");
        $hasCompany   = isset($data->company) && !empty($data->company);
        $hasCompanies = isset($data->companies) && !empty($data->companies) && count($data->companies) > 0;
        $seeLoginForm = $hasCompany || $hasCompanies;
    @endphp

    <div class="br-auth-login-page container-fluid min-vh-100 d-flex flex-column justify-content-center align-items-center">
        <div class="row w-100 shadow-lg rounded overflow-hidden" style="max-width: 480px;">
            <div class="col-lg-12 bg-white px-3 px-md-5 py-4 py-md-5">
                <header class="br-auth-login-header text-center">
                    <p class="br-auth-login-eyebrow mb-2">Bienvenido</p>
                    <h1 class="br-auth-login-title br-login-brand-name">
                        {{ $hasCompany ? $data->company->commercial_name : $ownerApp->commercial_name }}
                    </h1>
                    @if($seeLoginForm)
                        <p class="br-auth-login-sub mt-2 mb-0">Inicia sesión para continuar</p>
                    @endif
                </header>
                @if($seeLoginForm)
                    <form method="POST" action="{{ route('login') }}" class="mt-3 mt-md-4">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label colon-at-end fw-semibold">Correo electrónico</label>
                            <input type="text" class="form-control" id="email" name="email" placeholder="Ingrese el correo electrónico" :value="old('email')" required autofocus autocomplete="username"/>
                            @if($errors->get("email"))
                                @foreach((array) $errors->get("email") as $message)
                                    <small class="text-danger">{{ $message }}</small>
                                @endforeach
                            @endif
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label colon-at-end fw-semibold">Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="•••••••" required autocomplete="current-password"/>
                            @if($errors->get("password"))
                                @foreach((array) $errors->get("password") as $message)
                                    <small class="text-danger">{{ $message }}</small>
                                @endforeach
                            @endif
                        </div>
                        @if($hasCompany)
                            <div class="mb-3 d-none">
                                <label for="company_id" class="form-label colon-at-end fw-semibold">Empresa</label>
                                <select class="form-control" id="company_id" name="company_id" :value="old('company_id')" required>
                                    <option value="{{ $data->company->id }}" selected>{{ $data->company->commercial_name }}</option>
                                </select>
                                @if($errors->get("company_id"))
                                    @foreach ((array) $errors->get("company_id") as $message)
                                        <small class="text-danger">{{ $message }}</small>
                                    @endforeach
                                @endif
                            </div>
                        @endif
                        @if($hasCompanies)
                            <div class="mb-3">
                                <label for="company_id" class="form-label colon-at-end fw-semibold">Empresa</label>
                                <select class="form-control" id="company_id" name="company_id" :value="old('company_id')" required>
                                    <option value="">Selecciona tu empresa</option>
                                    @foreach($data->companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->commercial_name }}</option>
                                    @endforeach
                                </select>
                                @if($errors->get("company_id"))
                                    @foreach((array) $errors->get("company_id") as $message)
                                        <small class="text-danger">{{ $message }}</small>
                                    @endforeach
                                @endif
                            </div>
                        @endif
                        <div class="mb-1">
                            <div class="cf-turnstile" data-sitekey="{{ config("app.CAPTCHA_KEY_FRONTEND") }}" data-size="flexible"></div>
                            @if($errors->get("captcha"))
                                @foreach((array) $errors->get('captcha') as $message)
                                    <small class="text-danger">{{ $message }}</small>
                                @endforeach
                            @endif
                        </div>
                        <div class="d-flex justify-content-center align-items-center flex-wrap">
                            <button class="btn btn-primary waves-effect w-100" type="submit">
                                <i class="fa fa-sign-in"></i>
                                <span class="ms-2">Iniciar sesión</span>
                            </button>
                        </div>
                    </form>
                @else
                    <div class="alert alert-danger d-flex align-items-center mt-3" role="alert">
                        <span class="alert-icon rounded">
                            <i class="ti ti-ban"></i>
                        </span>
                        <span class="ms-3">
                            Actualice su <b>membresía</b> para acceder.
                        </span>
                    </div>
                    <hr/>
                    <div class="text-center mt-3 mt-md-4">
                        <small class="text-muted">
                            ¿Tienes problemas para acceder? Escríbenos a
                            <a href="mailto:{{ $ownerApp->support->email }}" class="br-link">{{ $ownerApp->support->email }}</a> o llámanos al
                            <a href="tel:{{ $ownerApp->support->phone }}" class="br-link">{{ $ownerApp->support->phone }}</a> para ayudarte a recuperar tu acceso.
                        </small>
                    </div>
                @endif
            </div>
        </div>
        @if($seeLoginForm)
            <div class="text-center mt-3 mt-md-4">
                <small class="text-muted">
                    ¿Necesitas ayuda? Escríbenos a
                    <a href="mailto:{{ $ownerApp->support->email }}" class="br-link">{{ $ownerApp->support->email }}</a> o llámanos al
                    <a href="tel:{{ $ownerApp->support->phone }}" class="br-link">{{ $ownerApp->support->phone }}</a>
                </small>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const form = document.querySelector("form");

            form?.addEventListener("submit", function(e) {

                const captchaResponse = document.querySelector(`input[name="cf-turnstile-response"]`);

                if(!captchaResponse || captchaResponse?.value === "") {

                    e.preventDefault();

                    Swal.fire({
                        icon              : "warning",
                        allowOutsideClick : false,
		                allowEscapeKey    : false,
                        html              : `<span class="d-block fw-bold">Captcha requerido</span> <span class="d-block mt-2">Por favor, completa el captcha para continuar.</span>`,
                        confirmButtonText : "Entendido",
                        customClass: {
                            confirmButton: "btn btn-primary waves-effect"
                    }});

                }

            });

        });
    </script>

</x-system-guest-layout>
