<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/championships'); // Redireciona para a página inicial
});

require_once ('web/championships.php');
require_once ('web/teams.php');

Route::get('/settings', function () {
    return Inertia::render('Settings'); // Aqui renderizará a página de Settings
})->name('settings');
