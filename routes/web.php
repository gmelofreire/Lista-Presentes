<?php

use App\Http\Controllers\AmizadeController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmailVerifiedCheckController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\ListaController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PresenteController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('welcome');

// Rota de verificação - redireciona para dashboard ou página de verificação pendente
Route::get('/home', [EmailVerifiedCheckController::class, '__invoke'])->middleware('auth')->name('home');

// Rotas autenticadas e verificadas
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/perfil', [PerfilController::class, 'edit'])->name('perfil.edit');
    Route::post('/perfil', [PerfilController::class, 'update'])->name('perfil.update');
    Route::delete('/perfil', [PerfilController::class, 'destroy'])->name('perfil.destroy');

    Route::resource('/amizades', AmizadeController::class)->except(['store']);
    Route::post('/amizades/{id}', [AmizadeController::class, 'store'])->name('amizade.store');
    Route::post('/amizades/{id}/reject', [AmizadeController::class, 'reject'])->name('amizade.reject');
    Route::post('/amizades/{id}/cancel', [AmizadeController::class, 'cancel'])->name('amizade.cancel');
    Route::post('/amizades/{id}/block', [AmizadeController::class, 'block'])->name('amizade.block');
    Route::post('/amizades/{id}/unblock', [AmizadeController::class, 'unblock'])->name('amizade.unblock');

    Route::resource('/notificacoes', NotificacaoController::class)->only(['index']);
    Route::get('/notificacoes/nao-lidas', [NotificacaoController::class, 'getNaoLidas'])->name('notificacoes.nao-lidas');
    Route::get('/notificacoes/recentes', [NotificacaoController::class, 'getRecentes'])->name('notificacoes.recentes');
    Route::post('/notificacoes/{id}/marcar-lido', [NotificacaoController::class, 'marcarLido'])->name('notificacoes.marcar-lido');
    Route::post('/notificacoes/marcar-todas-lido', [NotificacaoController::class, 'marcarTodasLido'])->name('notificacoes.marcar-todas-lido');

    Route::get('/api/usuarios/buscar', [AmizadeController::class, 'buscarUsuarios'])->name('usuarios.buscar');

    Route::resource('/listas', ListaController::class)->except(['update']);
    Route::post('/listas/{id}', [ListaController::class, 'update'])->name('listas.update');
    Route::get('/listas/{listaId}/participar', [ListaController::class, 'participar'])->name('listas.participar');
    Route::post('/listas/{listaId}/convite', [ListaController::class, 'gerarLinkConvite'])->name('listas.convite');
    Route::get('/listas/{listaId}/participantes/buscar', [ListaController::class, 'buscarParticipantes'])->name('listas.participantes.buscar');
    Route::post('/listas/{listaId}/participantes', [ListaController::class, 'adicionarParticipante'])->name('listas.participantes.store');
    Route::post('/listas/{listaId}/participantes/{participanteId}/papel', [ListaController::class, 'atualizarPapel'])->name('listas.participantes.papel');
    Route::delete('/listas/{listaId}/participantes/{participanteId}', [ListaController::class, 'removerParticipante'])->name('listas.participantes.destroy');

    Route::resource('/presentes', PresenteController::class)->except(['update', 'create']);
    Route::get('/presentes/create/{lista_id}', [PresenteController::class, 'create'])->name('presentes.create');
    Route::post('/presentes/update/{id}', [PresenteController::class, 'update'])->name('presentes.update');
    Route::post('/presentes/{id}/reservar', [PresenteController::class, 'reservar'])->name('presentes.reservar');
    Route::delete('/presentes/{id}/reservar', [PresenteController::class, 'liberar'])->name('presentes.liberar');
    Route::post('/presentes/buscar-dados-url', [PresenteController::class, 'buscarDadosUrl'])->middleware('throttle:20,1')->name('presentes.buscar-dados-url');

    Route::resource('/categorias', CategoriaController::class);
    Route::post('/categorias/reorder', [CategoriaController::class, 'reorder'])->name('categorias.reorder');

    Route::resource('/grupos', GrupoController::class)->except(['update', 'destroy']);
    Route::post('/grupos/{id}', [GrupoController::class, 'update'])->name('grupos.update');
    Route::delete('/grupos/{id}', [GrupoController::class, 'destroy'])->name('grupos.destroy');
    Route::post('/grupos/{id}/leave', [GrupoController::class, 'leave'])->name('grupos.leave');
    Route::post('/grupos/{id}/remove-member', [GrupoController::class, 'removeMember'])->name('grupos.removeMember');
    Route::post('/grupos/{id}/make-admin', [GrupoController::class, 'makeAdmin'])->name('grupos.makeAdmin');
    Route::post('/grupos/{id}/invite-link', [GrupoController::class, 'generateInviteLink'])->name('grupos.inviteLink');
    Route::get('/grupos/join/{token}', [GrupoController::class, 'joinByToken'])->name('grupos.join');
    Route::post('/grupos/{id}/invite-friends', [GrupoController::class, 'inviteFriends'])->name('grupos.inviteFriends');
});

require __DIR__.'/auth.php';
