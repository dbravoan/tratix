<?php

namespace App\Providers;

use App\Services\Billing\BillingGateway;
use App\Services\Billing\DemoGateway;
use App\Services\Billing\StripeGateway;
use App\Services\TsaService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TsaService::class, fn () => new TsaService(
            url: (string) config('services.tsa.url'),
            timeout: (int) config('services.tsa.timeout', 6),
        ));

        $this->app->singleton(BillingGateway::class, function () {
            if (config('billing.gateway') === 'stripe' && config('billing.stripe.secret')) {
                return new StripeGateway;
            }

            return new DemoGateway;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
