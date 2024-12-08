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
            'App\Contracts\ChampionshipContract',
            'App\Services\Championship\ChampionshipService'
        );

        $this->app->bind(
            'App\Contracts\TeamContract',
            'App\Services\Team\TeamService'
        );

        this->app->bind(
            'App\Contracts\AnalyticsContract\TeamAnalyticsContract',
            'App\Services\Analytics\TeamAnalyticsService'
        );

        $this->app->bind(
            'App\Contracts\PlayerRateContract',
            'App\Services\Player\PlayerRateService'
        );

        $this->app->bind(
            'App\Contracts\FixtureContract',
            'App\Services\Fixture\FixtureService'
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
