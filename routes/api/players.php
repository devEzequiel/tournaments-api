<?php

use App\Http\Controllers\Api\Player\PlayerController;
use Illuminate\Support\Facades\Route;

Route::controller(PlayerController::class)->group(function () {
    Route::apiResource('players', PlayerController::class);
});
