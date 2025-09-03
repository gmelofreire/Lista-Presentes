<?php

use App\Http\Controllers\ListaController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PresenteController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect(route('dashboard'));
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard', [
        'title' => 'Página Inicial'
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::post('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::delete('/perfil', [PerfilController::class, 'destroy'])->name('perfil.destroy');

    Route::resource('/listas', ListaController::class)->except(['update']);
    Route::post('/listas/{id}', [ListaController::class, 'update'])->name('listas.update');
    
    Route::resource('/presentes', PresenteController::class)->except(['update', 'create']);
    Route::get('/presentes/create/{lista_id}', [PresenteController::class, 'create'])->name('presentes.create');
    Route::post('/presentes/update/{id}', [PresenteController::class, 'update'])->name('presentes.update');
});

require __DIR__.'/auth.php';
