<?php

use App\Modules\Championship\ChampionshipController;
use Illuminate\Support\Facades\Route;

Route::controller(ChampionshipController::class)->group(function () {
    Route::get('championship/{championship_id}/fixtures', 'getFixtures');
    Route::apiResource('championship', ChampionshipController::class);
});
