<?php

namespace App\Providers;

use App\Services\Empic\EmpicCmService;
use App\Services\OneVerify\OneVerifyService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OneVerifyService::class);
        $this->app->singleton(EmpicCmService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
