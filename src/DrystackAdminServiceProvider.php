<?php


namespace Drystack\Admin;

use Drystack\Admin\Commands\DrystackAdminInit;
use Drystack\Admin\Commands\MakeCrudPage;
use Drystack\Admin\Commands\MakeAuth;
use Illuminate\Support\ServiceProvider;

class DrystackAdminServiceProvider extends ServiceProvider {
    public function register() {
        $this->mergeConfigFrom(__DIR__.'/../config/drystack.php', 'drystack');
    }

    public function boot() {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DrystackAdminInit::class,
                MakeCrudPage::class,
                MakeAuth::class
            ]);
            $this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/drystack'),
            ], 'drystack-lang');
            $this->publishes([
                __DIR__.'/../config/drystack.php' => config_path('drystack.php'),
            ], 'drystack-config');
        }
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'dry-admin');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'drystack');
    }
}
