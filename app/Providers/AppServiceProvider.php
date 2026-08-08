<?php

namespace App\Providers;

use App\Models\System\Finance\CashRegister;
use App\Models\System\Finance\PaymentMethod;
use App\Models\System\Finance\PaymentMethodVariant;
use App\Models\System\Finance\Tax;
use App\Models\System\Organizations\Branch;
use App\Models\System\Organizations\CompanySetting;
use App\Models\System\Organizations\CompanySubSection;
use App\Models\System\Organizations\Role;
use App\Models\System\Organizations\RoleSubSection;
use App\Models\System\Organizations\User;
use App\Observers\System\Organizations\BusinessAuditObserver;
use App\Observers\System\Organizations\CompanySubSectionObserver;
use App\Observers\System\Organizations\RoleObserver;
use App\Observers\System\Organizations\RoleSubSectionObserver;
use App\Services\System\Tenancy\TenantContext;
use App\View\Components\System\{SystemGuestLayout};
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    public function register(): void {
        $this->app->singleton(TenantContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        Blade::component("system-guest-layout", SystemGuestLayout::class);
        CompanySubSection::observe(CompanySubSectionObserver::class);
        Role::observe(RoleObserver::class);
        RoleSubSection::observe(RoleSubSectionObserver::class);

        foreach ([
            Branch::class,
            CashRegister::class,
            CompanySetting::class,
            PaymentMethod::class,
            PaymentMethodVariant::class,
            Role::class,
            RoleSubSection::class,
            Tax::class,
            User::class,
        ] as $auditedModel) {
            $auditedModel::observe(BusinessAuditObserver::class);
        }
    }
}
