<?php

namespace App\Providers;

use App\Services\Attributes\AttributeInterface;
use App\Services\Attributes\AttributeService;
use App\Services\Audits\AuditInterface;
use App\Services\Audits\AuditService;
use App\Services\Categories\CategoryInterface;
use App\Services\Categories\CategoryService;
use App\Services\Companies\CompanyInterface;
use App\Services\Companies\CompanyService;
use App\Services\Contacts\ContactInterface;
use App\Services\Contacts\ContactService;
use App\Services\Customer\CustomerInterface;
use App\Services\Customer\CustomerService;
use App\Services\Products\ProductInterface;
use App\Services\Products\ProductService;
use App\Services\Shops\ShopInterface;
use App\Services\Shops\ShopService;
use App\Services\Warehouses\WarehouseInterface;
use App\Services\Warehouses\WarehouseService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AttributeInterface::class, AttributeService::class);
        $this->app->bind(AuditInterface::class, AuditService::class);
        $this->app->bind(CategoryInterface::class, CategoryService::class);
        $this->app->bind(CompanyInterface::class, CompanyService::class);
        $this->app->bind(ContactInterface::class, ContactService::class);
        $this->app->bind(CustomerInterface::class, CustomerService::class);
        $this->app->bind(ProductInterface::class, ProductService::class);
        $this->app->bind(ShopInterface::class, ShopService::class);
        $this->app->bind(WarehouseInterface::class, WarehouseService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        Blade::if('taskLocked', function ($task) {
            return (bool)($task?->complete);
        });

        Blade::if('taskOpen', function ($task) {
            return ! (bool)($task?->complete);
        });

        Blade::if('canAccess', function (string $resource, ...$needs) {
            $user = auth()->user();
            if (!$user) return false;
            return \App\Services\Access::can($user, $resource, $needs);
        });
    }
}
