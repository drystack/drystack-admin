<?php


namespace Drystack\Crud;


use Drystack\Crud\Commands\MakeCrudPage;
use Illuminate\Support\ServiceProvider;

class CrudServiceProvider extends ServiceProvider {
    public function boot() {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeCrudPage::class
            ]);

            $this->publishes([
                __DIR__.'/../resources/views/crud' => resource_path('views/vendor/drystack/crud'),
            ], 'drystack-views');
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'drystack');
    }
}
