<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\System\Tenancy\{TenantConnectionManager, TenantContext, TenantResolver};
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class ResolveTenant {

    public function __construct(
        private readonly TenantResolver $resolver,
        private readonly TenantConnectionManager $connectionManager,
        private readonly TenantContext $context
    ) {}

    public function handle(Request $request, Closure $next): Response {

        $tenant = $this->resolver->resolveByHost($request->getHost());

        if(!$tenant) {
            $this->context->set(null);
            Config::set('session.cookie', $this->centralSessionCookieName());
            return $next($request);
        }

        $this->connectionManager->connect($tenant);
        $this->context->set($tenant);
        Config::set('session.cookie', $this->tenantSessionCookieName($tenant->slug));
        Config::set('session.domain', null);

        return $next($request);

    }

    private function centralSessionCookieName(): string {

        return env('SESSION_COOKIE') ?: Str::slug(env('APP_NAME', 'laravel'), '_') . '_session';

    }

    private function tenantSessionCookieName(string $slug): string {

        $prefix = (string) config('tenancy.session_cookie_prefix', 'gympe_tenant');
        $hash = substr(hash('sha256', $slug), 0, 12);

        return Str::slug("{$prefix}_{$slug}_{$hash}", '_') . '_session';

    }

}
