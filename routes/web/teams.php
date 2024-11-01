<?php

use App\Http\Controllers\Api\Team\TeamController;
use Illuminate\Support\Facades\Route;

Route::controller(TeamController::class)->group(function () {
        Route::get('team')->name('team.create');
    });
