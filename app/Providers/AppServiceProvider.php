<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Model::shouldBeStrict($this->app->isLocal());

        // Surface N+1 queries and accidental lazy loading during development
        // rather than shipping them to production silently.
        DB::prohibitDestructiveCommands($this->app->isProduction());
    }
}
