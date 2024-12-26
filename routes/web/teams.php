<?php

use App\Modules\Team\TeamController;
use Illuminate\Support\Facades\Route;

Route::controller(TeamController::class)->prefix('teams')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->name('team.list');
        Route::get('/{team_name}', [TeamController::class, 'show'])->name('team.show');
    });
