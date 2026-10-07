<?php

namespace App\Providers;

use App\Contracts\SmsServiceInterface;
use App\Contracts\WhatsAppServiceInterface;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Shop;
use App\Policies\ProductPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\ShopPolicy;
use App\Services\Messaging\DeviceSmsService;
use App\Services\Messaging\DeviceWhatsAppService;
use App\Services\Messaging\TwilioSmsService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WhatsAppServiceInterface::class, DeviceWhatsAppService::class);
        $this->app->bind(SmsServiceInterface::class, function () {
            return config('services.twilio.enabled')
                ? $this->app->make(TwilioSmsService::class)
                : $this->app->make(DeviceSmsService::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Shop::class, ShopPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(PurchaseOrder::class, PurchaseOrderPolicy::class);
    }
}
