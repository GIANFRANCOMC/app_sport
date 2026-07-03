<?php

namespace App\Providers;

use App\Models\System\Finance\{CashRegister, PaymentMethod, Tax};
use App\Models\System\Organizations\{Branch, CompanySetting, CompanySubSection, Role, RoleSubSection, User};
use App\Observers\System\Organizations\{
    BusinessAuditObserver,
    CompanySubSectionObserver,
    RoleObserver,
    RoleSubSectionObserver
};
use App\View\Components\System\{SystemGuestLayout};
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Services\System\Tenancy\TenantContext;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::component('system-guest-layout', SystemGuestLayout::class);
        CompanySubSection::observe(CompanySubSectionObserver::class);
        Role::observe(RoleObserver::class);
        RoleSubSection::observe(RoleSubSectionObserver::class);

        foreach([
            Branch::class,
            CashRegister::class,
            CompanySetting::class,
            PaymentMethod::class,
            Role::class,
            RoleSubSection::class,
            Tax::class,
            User::class
        ] as $auditedModel) {
            $auditedModel::observe(BusinessAuditObserver::class);
        }
    }
}
