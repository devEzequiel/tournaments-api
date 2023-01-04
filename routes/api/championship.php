<?php

use App\Http\Controllers\Api\Championship\ChampionshipController;
use Illuminate\Support\Facades\Route;

Route::controller(ChampionshipController::class)->group(function () {
    Route::get('championship/{championship_id}/fixtures', 'getFixtures');
    Route::get('championship/fixture/{fixture_id}', 'showFixture');
    Route::apiResource('championship', ChampionshipController::class);
});
