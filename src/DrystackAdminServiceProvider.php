<?php


namespace Drystack\Admin;

use Drystack\Admin\Commands\DrystackAdminInit;
use Drystack\Admin\Commands\MakeCrudPage;
use Illuminate\Support\ServiceProvider;

class DrystackAdminServiceProvider extends ServiceProvider {
    public function register() {
        $this->mergeConfigFrom(__DIR__.'/../config/drystack.php', 'drystack');
    }

    public function boot() {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DrystackAdminInit::class,
                MakeCrudPage::class
            ]);
            $this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/drystack'),
            ], 'drystack-lang');
            $this->publishes([
                __DIR__.'/../config/drystack.php' => config_path('drystack.php'),
            ], 'drystack-config');
        }

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'drystack');
    }
}
