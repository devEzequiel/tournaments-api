<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Rotas de autenticação
require_once ('auth.php');

// Redireciona para a página inicial
Route::get('/', function () {
    return redirect('/championships');
});

// Rotas protegidas por autenticação
Route::middleware('auth')->group(function () {
    require_once ('web/championships.php');
    require_once ('web/teams.php');
    require_once ('web/players.php');

    Route::get('/settings', function () {
        return Inertia::render('Settings');
    })->name('settings');
});
