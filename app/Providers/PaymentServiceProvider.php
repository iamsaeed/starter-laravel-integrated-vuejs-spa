<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Razorpay\Api\Api as RazorpayApi;
use Stripe\StripeClient;

class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(StripeClient::class, function () {
            return new StripeClient(config('services.stripe.secret'));
        });

        $this->app->singleton(RazorpayApi::class, function () {
            return new RazorpayApi(
                config('services.razorpay.key'),
                config('services.razorpay.secret')
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
