<?php

namespace App\Providers;

use App\Repositories\PricingRepository;
use App\Repositories\PricingRepositoryInterface;
use App\Repositories\TransactionRepositoryInterface;
use App\Services\TransactionService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PricingRepositoryInterface::class, PricingRepository::class);
        $this->app->singleton(TransactionRepositoryInterface::class, TransactionService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
