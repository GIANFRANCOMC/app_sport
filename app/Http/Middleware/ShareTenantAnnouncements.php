<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\System\Tenancy\TenantAnnouncement;
use App\Services\System\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

final class ShareTenantAnnouncements {
    public function __construct(private readonly TenantContext $context) {
    }

    public function handle(Request $request, Closure $next): Response {
        $tenant = $this->context->get();
        $announcements = collect();

        if ($tenant && Schema::connection("landlord")->hasTable("tenant_announcements")) {
            $announcements = TenantAnnouncement::query()
                ->where("status", "active")
                ->where(fn ($query) => $query
                    ->whereNull("tenant_database_id")
                    ->orWhere("tenant_database_id", $tenant->id))
                ->where(fn ($query) => $query->whereNull("starts_at")->orWhere("starts_at", "<=", now()))
                ->where(fn ($query) => $query->whereNull("ends_at")->orWhere("ends_at", ">=", now()))
                ->orderByDesc("created_at")
                ->get();
        }

        view()->share("tenantAnnouncements", $announcements);

        return $next($request);
    }
}
