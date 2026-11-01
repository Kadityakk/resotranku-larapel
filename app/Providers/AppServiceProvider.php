<?php

namespace App\Providers;

use App\Models\Order;
use App\Services\Contracts\PaymentGateway;
use App\Services\MidtransGateway;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaymentGateway::class, fn () => new MidtransGateway([
            'merchant_id' => config('midtrans.merchant_id'),
            'client_key' => config('midtrans.client_key'),
            'server_key' => config('midtrans.server_key'),
            'is_production' => (bool) config('midtrans.is_production'),
            'is_sanitized' => (bool) config('midtrans.is_sanitized'),
            'is_3ds' => (bool) config('midtrans.is_3ds'),
        ]));

        $this->app->alias(PaymentGateway::class, MidtransGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->shareOrderCountsWithSidebar();
    }

    /**
     * Badge counts every staff page needs, so no controller has to pass them.
     */
    private function shareOrderCountsWithSidebar(): void
    {
        View::composer('admin.layouts.__sidebar', function ($view): void {
            $view->with([
                'pendingOrderCount' => Order::awaitingConfirmation()->count(),
                'kitchenOrderCount' => Order::inKitchen()->count(),
            ]);
        });
    }
}
