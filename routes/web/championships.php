<?php

use App\Modules\Championship\ChampionshipController;
use Illuminate\Support\Facades\Route;

Route::prefix('championships')->group(function () {
    Route::get('/', [ChampionshipController::class, 'index'])
        ->name('championships.index');

    Route::get('/{championship_id}/fixtures', [ChampionshipController::class, 'getFixtures'])
        ->name('championships.fixtures');

    Route::get('/{championship_name}', [ChampionshipController::class, 'show'])
        ->name('championships.show');
});
