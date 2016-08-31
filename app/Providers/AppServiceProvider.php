<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
    }

    /**
     * @return void
     */
    protected function registerRoutes()
    {
    }
}
