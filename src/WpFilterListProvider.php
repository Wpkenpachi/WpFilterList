<?php

namespace Wpkenpachi\Wpfilterlist;

use Illuminate\Support\ServiceProvider;

class WpFilterListProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        include __DIR__ . '/routes.php';
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('Wpkenpachi\Wpfilterlist\FilterList');
    }
}
