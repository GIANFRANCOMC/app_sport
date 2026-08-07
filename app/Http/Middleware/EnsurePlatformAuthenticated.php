<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\System\Tenancy\PlatformUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePlatformAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = (int) $request->session()->get('platform_user_id', 0);
        $user = $userId > 0
            ? PlatformUser::query()->whereKey($userId)->where('status', 'active')->first()
            : null;

        if(!$user) {
            $request->session()->forget('platform_user_id');
            return redirect()->route('platform.login');
        }

        $request->attributes->set('platformUser', $user);
        view()->share('platformUser', $user);

        return $next($request);
    }
}
