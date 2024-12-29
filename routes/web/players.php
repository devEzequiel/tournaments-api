<?php

use App\Modules\Player\PlayerController;
use Illuminate\Support\Facades\Route;

Route::controller(PlayerController::class)->prefix('players')->group(function () {
    Route::get('/', 'showTabs')->name('players.show-tabs');
});
