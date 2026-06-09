<?php

namespace App\Providers;

use App\Models\System\Organizations\CompanySubSection;
use App\Observers\System\Organizations\CompanySubSectionObserver;
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
    }
}
