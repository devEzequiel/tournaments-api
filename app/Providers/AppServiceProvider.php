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
            'App\Modules\Player\PlayerService'
        );

        $this->app->bind(
            'App\Contracts\ChampionshipContract',
            'App\Modules\Championship\ChampionshipService'
        );

        $this->app->bind(
            'App\Contracts\TeamContract',
            'App\Modules\Team\TeamService'
        );

        this->app->bind(
            'App\Contracts\AnalyticsContract\TeamAnalyticsContract',
            'App\Services\Analytics\TeamAnalyticsService'
        );

        $this->app->bind(
            'App\Contracts\PlayerRateContract',
            'App\Modules\Player\PlayerRateService'
        );

        $this->app->bind(
            'App\Contracts\FixtureContract',
            'App\Modules\Fixture\FixtureService'
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
