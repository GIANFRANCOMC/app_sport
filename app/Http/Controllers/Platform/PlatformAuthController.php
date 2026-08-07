<?php

declare(strict_types=1);

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\System\Tenancy\PlatformUser;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

final class PlatformAuthController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if($request->session()->has('platform_user_id')) {
            return redirect()->route('platform.tenants.index');
        }

        return view('Platform.auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'max:255'],
        ]);
        $user = PlatformUser::query()
            ->where('email', strtolower($credentials['email']))
            ->where('status', 'active')
            ->first();

        if(!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors(['email' => 'Las credenciales no son válidas.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $request->session()->put('platform_user_id', $user->id);
        $user->forceFill(['last_login_at' => now()])->save();

        return redirect()->intended(route('platform.tenants.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('platform.login');
    }
}
