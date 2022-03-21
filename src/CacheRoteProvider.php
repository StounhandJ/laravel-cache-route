<?php

namespace StounhandJ\LaravelCacheRoute;

use Illuminate\Support\ServiceProvider;
class CacheRoteProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

        if (! app()->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__.'/../config/stouncache.php', 'stouncache');
        }
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        if (app()->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../config/stouncache.php' => config_path('stouncache.php'),
            ], 'stounhandj-config');
        }
    }
}