<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Auth\Customer;
use App\Observers\CustomerObserver;

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
        // Register model observers
        Customer::observe(CustomerObserver::class);

    }
}
