<?php

use App\Modules\Championship\ChampionshipController;
use Illuminate\Support\Facades\Route;

Route::prefix('championships')->name('championships.')->group(function () {
    Route::get('/', [ChampionshipController::class, 'index'])->name('index');
    Route::get('/{championship_name}', [ChampionshipController::class, 'show'])->name('show');
});
