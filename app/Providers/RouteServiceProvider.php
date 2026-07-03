<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('guest-complaints', function(Request $request) {
            $company = $request->route('company_slug', 'unknown');

            return [
                Limit::perMinute(3)->by("complaint-minute:{$company}:{$request->ip()}"),
                Limit::perDay(5)->by("complaint-day:{$company}:{$request->ip()}")
            ];
        });

        RateLimiter::for('guest-status', fn(Request $request) =>
            Limit::perMinute(20)->by("complaint-status:{$request->route('company_slug')}:{$request->ip()}")
        );

        RateLimiter::for('guest-attendance', function(Request $request) {
            $company = $request->route('company_slug', 'unknown');

            return [
                Limit::perMinute(20)->by("attendance-minute:{$company}:{$request->ip()}"),
                Limit::perHour(120)->by("attendance-hour:{$company}:{$request->ip()}")
            ];
        });

        RateLimiter::for('biometric-events', fn(Request $request) =>
            Limit::perMinute(180)->by(
                "biometric:{$request->route('company_slug')}:" . ($request->header('X-Device-Key') ?: $request->ip())
            )
        );

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
