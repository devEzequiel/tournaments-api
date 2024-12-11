<?php

use App\Modules\Player\PlayerController;
use Illuminate\Support\Facades\Route;

Route::controller(PlayerController::class)->group(function () {
    Route::apiResource('player', PlayerController::class);
});
