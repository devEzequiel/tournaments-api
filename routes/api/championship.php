<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Championship\ChampionshipController;

Route::controller(ChampionshipController::class)->prefix('championship')
    ->group(function () {
        Route::get('{championship_id}/fixtures', 'getFixtures');
        Route::post('{championship_id}/generate-final-round', 'generateFinalRound');
        Route::post('/', 'store');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
        Route::get('/{championship_id}', 'detail');
        Route::get('/{championship_id}/awards', 'getAwards');
    });

Route::controller(\App\Modules\Championship\ChampionshipAnalyticController::class)
    ->prefix('championship')->group(function () {

        Route::get('standings/{championship_id}', 'getStandings');
        Route::get('players-stats/{championship_id}', 'getPlayersStats');
        Route::get('team-stats/{championship_id}', 'getTeamStats');
        Route::get('table-data/{championship_id}', 'getTableData');
        Route::post('clashes', 'getClashes');
    });
