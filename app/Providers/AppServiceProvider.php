<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;


use App\Services\VisitorService;
use App\Services\Contracts\VisitorService as VisitorServiceContract;

use App\Services\Contracts\SiteLinkService as SiteLinkServiceContract;
use App\Services\SiteLinkService;


use App\Services\Contracts\SiteService as SiteServiceContract;
use App\Services\SiteService;



class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(VisitorServiceContract::class, VisitorService::class);
        $this->app->bind(SiteLinkServiceContract::class, SiteLinkService::class);
        $this->app->bind(SiteServiceContract::class, SiteService::class);

    }
}
