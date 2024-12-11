<?php

use App\Modules\Fixture\FixtureController;
use Illuminate\Support\Facades\Route;

Route::controller(FixtureController::class)->prefix('fixtures')->group(function () {
    Route::get('/unplayed/(id}', 'getUnplayedFixtures');
    Route::get('/{id}', 'getFixtures');
    Route::post('/', 'playMatch');
});
