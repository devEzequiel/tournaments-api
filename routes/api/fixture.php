<?php

use App\Modules\Fixture\FixtureController;
use Illuminate\Support\Facades\Route;

Route::controller(FixtureController::class)->prefix('fixtures')->group(function () {
    Route::get('/{id}/unplayed', 'getUnplayedFixtures');
    Route::get('/{id}', 'getFixtures');
    Route::get('/{id}/basic', 'getFixturesWithBasicInfo');
    Route::put('/', 'playMatch');
});
