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
    public function register(): void
    {
        $this->app->bind(
            'App\Contracts\PlayerContract',
            'App\Services\Player\PlayerService'
        );

        $this->app->bind(
            'App\Contracts\TeamContract',
            'App\Services\Team\TeamService'
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
