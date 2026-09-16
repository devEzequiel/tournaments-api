<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Consumed only by this app's own Vue frontend, which authenticates with the
| session cookie via Sanctum's stateful middleware (see bootstrap/app.php).
| Everything in here requires an authenticated user — there are no public
| endpoints.
|
*/

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());

    require __DIR__.'/api/teams.php';
    require __DIR__.'/api/players.php';
    require __DIR__.'/api/championship.php';
    require __DIR__.'/api/fixture.php';
});
