<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Policies\CustomerPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\OrderPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Model::shouldBeStrict(! app()->isProduction());

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Order::class,    OrderPolicy::class);
        Gate::policy(Invoice::class,  InvoicePolicy::class);
    }
}
