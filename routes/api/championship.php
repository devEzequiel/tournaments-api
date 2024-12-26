<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Championship\ChampionshipController;

Route::controller(ChampionshipController::class)->prefix('championship')
    ->group(function () {
    Route::get('{championship_id}/fixtures', 'getFixtures');
    Route::apiResource('/', ChampionshipController::class);
});

Route::controller(\App\Modules\Championship\ChampionshipAnalyticController::class)
    ->prefix('championship')->group(function () {

        Route::get('standings/{championship_id}', 'getStandings');
        Route::get('players-stats/{championship_id}', 'getPlayersStats');
        Route::get('team-stats/{championship_id}', 'getTeamStats');
        Route::get('table-data/{championship_id}', 'getTableData');
        Route::get('clashes', 'getClashes');
    });
