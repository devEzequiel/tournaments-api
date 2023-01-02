<?php

use App\Http\Controllers\Api\Championship\ChampionshipController;
use Illuminate\Support\Facades\Route;

Route::controller(ChampionshipController::class)->group(function () {
    Route::apiResource('championship', ChampionshipController::class);
});
