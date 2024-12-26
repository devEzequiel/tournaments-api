<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect('/championships'); // Redireciona para a página inicial
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->name('dashboard');

Route::get('/championships', function () {
    // Aqui simulamos dados dos campeonatos para enviar ao componente Vue
    $championships = [
        ['id' => 1, 'name' => 'Championship A', 'status' => 'Active'],
        ['id' => 2, 'name' => 'Championship B', 'status' => 'In Progress'],
        ['id' => 3, 'name' => 'Championship C', 'status' => 'Finished'],
    ];

    return Inertia::render('Championships/Index', [
        'championships' => $championships
    ]);
})->name('championships');

Route::get('/settings', function () {
    return Inertia::render('Settings'); // Aqui renderizará a página de Settings
})->name('settings');
