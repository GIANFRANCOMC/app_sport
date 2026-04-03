<?php

declare(strict_types=1);

namespace App\Http\Controllers\System\Auth;

use App\Helpers\System\Utilities;
use App\Http\Controllers\Controller;
use App\Http\Requests\System\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\{Request, RedirectResponse};
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

use App\Models\System\Organizations\{Company};

class AuthenticatedSessionController extends Controller {

    /**
     * Display the login view.
     */
    public function create(Request $request): View {

        $data = Utilities::getDefaultData();

        $data->company   = null;
        $data->companies = [];

        if(Utilities::isDefined($data->env_company_id)) {

            $data->company = Company::where("id", $data->env_company_id)
                                    ->whereIn("status", ["active"])
                                    ->with(["socialsMedia"])
                                    ->first();

        }else {

            $base64Company = $request->company;

            if(Utilities::isDefined($base64Company)) {

                $companyId = base64_decode($base64Company);

                if(Utilities::isDefined($companyId)) {

                    $data->company = Company::where("id", $companyId)
                                            ->whereIn("status", ["active"])
                                            ->with(["socialsMedia"])
                                            ->first();

                }

            }else {

                $data->companies = Company::whereIn("status", ["active"])
                                          ->with(["socialsMedia"])
                                          ->orderBy("commercial_name", "ASC")
                                          ->get();

            }

        }

        return view("System/auth/login", compact("data"));

    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse {

        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(RouteServiceProvider::HOME);

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse {

        $user    = Auth::user();
        $company = $user->company;

        Auth::guard("web")->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $query = "?company=".base64_encode((string) $company->id);

        return redirect("/".$query);

    }

}
