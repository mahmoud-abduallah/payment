<?php

namespace Nafezly\Payments;

use Illuminate\Support\ServiceProvider;
use Nafezly\Payments\Classes\FawryPayment;
use Nafezly\Payments\Classes\FawryWalletPayment;
use Nafezly\Payments\Classes\HyperPayPayment;
use Nafezly\Payments\Classes\KashierPayment;
use Nafezly\Payments\Classes\PaymobPayment;
use Nafezly\Payments\Classes\PayPalPayment;
use Nafezly\Payments\Classes\PaytabsPayment;
use Nafezly\Payments\Classes\ThawaniPayment;
use Nafezly\Payments\Classes\TapPayment;
use Nafezly\Payments\Classes\OpayPayment;
use Nafezly\Payments\Classes\PaymobWalletPayment;
use Nafezly\Payments\Classes\PaymobKioskPayment;
use Nafezly\Payments\Classes\QNPPayment;

class NafezlyPaymentsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->configure();
        $this->registerPublishing();
        
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nafezly');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'nafezly');
        

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/nafezly'),
        ]);
        $this->publishes([
            __DIR__ . '/../config/nafezly-payments.php' => config_path('nafezly-payments.php'),
        ]);
        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/payments'),
        ]);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->bind(PaymobPayment::class, function () {
            return new PaymobPayment();
        });
        $this->app->bind(FawryPayment::class, function () {
            return new FawryPayment();
        });
        
        
        $this->app->bind(QNPPayment::class, function () {
            return new QNPPayment();
        });
        
        $this->app->bind(FawryWalletPayment::class, function () {
            return new FawryWalletPayment();
        });
        $this->app->bind(ThawaniPayment::class, function () {
            return new ThawaniPayment();
        });
        $this->app->bind(PaypalPayment::class, function () {
            return new PaypalPayment();
        });
        $this->app->bind(HyperPayPayment::class, function () {
            return new HyperPayPayment();
        });
        $this->app->bind(KashierPayment::class, function () {
            return new KashierPayment();
        });
        $this->app->bind(TapPayment::class, function () {
            return new TapPayment();
        });
        $this->app->bind(OpayPayment::class, function () {
            return new OpayPayment();
        });
        $this->app->bind(PaymobKioskPayment::class, function () {
            return new PaymobKioskPayment();
        });
        $this->app->bind(PaymobWalletPayment::class, function () {
            return new PaymobWalletPayment();
        });
        $this->app->bind(PaytabsPayment::class, function () {
            return new PaytabsPayment();
        });
        
    }

    /**
     * Setup the configuration for Nafezly Payments.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/nafezly-payments.php', 'nafezly-payments'
        );
    }

    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    protected function registerPublishing()
    {
        $this->publishes([
            __DIR__ . '/../config/nafezly-payments.php' => config_path('nafezly-payments.php'),
        ], 'nafezly-payments-config');
        $this->publishes([
            __DIR__ . '/../resources/lang' => resource_path('lang/vendor/payments'),
        ], 'nafezly-payments-lang');
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/payments'),
        ], 'nafezly-payments-views');

    }
}