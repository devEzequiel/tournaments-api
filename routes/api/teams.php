<?php

use App\Modules\Team\TeamController;
use Illuminate\Support\Facades\Route;

Route::controller(TeamController::class)->group(function () {
    Route::apiResource('team', TeamController::class)
        ->only(['store', 'update', 'destroy']);

    Route::get('team/{id}/detail', 'detail')->name('team.detail');
});
