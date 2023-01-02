<?php

use Illuminate\Support\Facades\Route;

Route::controller(\App\Http\Controllers\Api\UtilsController::class)
    ->prefix('utils')->group(function () {
        Route::get('/check', 'healthCheck');
    });
