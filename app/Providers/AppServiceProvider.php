<?php

namespace App\Providers;

use App\Models\System\Organizations\{CompanySubSection, Role, RoleSubSection};
use App\Observers\System\Organizations\{CompanySubSectionObserver, RoleObserver, RoleSubSectionObserver};
use App\View\Components\System\{SystemGuestLayout};
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
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
    }
}
