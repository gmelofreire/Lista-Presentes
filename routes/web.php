<?php

use App\Http\Controllers\ListaController;
use App\Http\Controllers\PerfilController;
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

    Route::resource('/listas', ListaController::class);
});

require __DIR__.'/auth.php';
