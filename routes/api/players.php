<?php

use App\Modules\Player\PlayerController;
use Illuminate\Support\Facades\Route;

Route::controller(PlayerController::class)->group(function () {
    Route::apiResource('player', PlayerController::class)
    ->except(['update']);

    Route::put('player/change-team', 'changeTeam')->name('player.change-team');
});

Route::controller(\App\Modules\Player\PlayerAnalyticController::class)->prefix('players/analytic')->group(function () {
    Route::get('/', 'getAnalytics');
});
