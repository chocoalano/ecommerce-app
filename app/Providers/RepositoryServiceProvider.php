<?php

namespace App\Providers;

use App\Contracts\OrderRepository;
use App\Repositories\Orders\EloquentOrderRepository;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Repositories\BaseRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Services\ProductServiceInterface;
use App\Contracts\Services\CategoryServiceInterface;
use App\Repositories\BaseRepository;
use App\Repositories\Product\ProductRepository;
use App\Repositories\Product\CategoryRepository;
use App\Services\Product\ProductService;
use App\Services\Product\CategoryService;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Base Repository
        $this->app->bind(BaseRepositoryInterface::class, BaseRepository::class);

        // Product Repository & Service
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(ProductServiceInterface::class, ProductService::class);

        // Category Repository & Service
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(CategoryServiceInterface::class, CategoryService::class);

        $this->app->bind(OrderRepository::class, EloquentOrderRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
