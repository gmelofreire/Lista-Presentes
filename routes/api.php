<?php

use App\Http\Controllers\Api\UsernameController;
use App\Http\Controllers\Api\V1;
use Illuminate\Support\Facades\Route;

Route::get('/check-username', [UsernameController::class, 'checkUsername'])->middleware('throttle:30,1');

Route::prefix('v1')->name('api.v1.')->group(function () {
    // Públicas (limite por IP)
    Route::middleware('throttle:auth')->group(function () {
        Route::post('auth/register', [V1\AuthController::class, 'register']);
        Route::post('auth/login', [V1\AuthController::class, 'login']);
        Route::post('auth/esqueci-senha', [V1\AuthController::class, 'esqueciSenha']);
    });

    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
        Route::post('auth/logout', [V1\AuthController::class, 'logout']);
        Route::get('me', [V1\AuthController::class, 'me']);

        Route::get('listas', [V1\ListaController::class, 'index']);
        Route::post('listas', [V1\ListaController::class, 'store']);
        Route::get('listas/{lista}', [V1\ListaController::class, 'show']);
        Route::match(['put', 'patch'], 'listas/{lista}', [V1\ListaController::class, 'update']);
        Route::post('listas/{lista}/atualizar', [V1\ListaController::class, 'update']);
        Route::delete('listas/{lista}', [V1\ListaController::class, 'destroy']);
        Route::post('listas/{lista}/convite', [V1\ListaController::class, 'convite']);
        Route::post('listas/{lista}/entrar', [V1\ListaController::class, 'entrar']);
        Route::post('listas/{lista}/participantes', [V1\ListaController::class, 'adicionarParticipante']);
        Route::patch('listas/{lista}/participantes/{participante}', [V1\ListaController::class, 'alterarPapel']);
        Route::delete('listas/{lista}/participantes/{participante}', [V1\ListaController::class, 'removerParticipante']);

        Route::get('listas/{lista}/presentes', [V1\PresenteController::class, 'index']);
        Route::post('listas/{lista}/presentes', [V1\PresenteController::class, 'store']);
        Route::post('presentes/buscar-dados-url', [V1\PresenteController::class, 'buscarDadosUrl'])->middleware('throttle:20,1');
        Route::get('presentes/{presente}', [V1\PresenteController::class, 'show']);
        Route::match(['put', 'patch'], 'presentes/{presente}', [V1\PresenteController::class, 'update']);
        Route::post('presentes/{presente}/atualizar', [V1\PresenteController::class, 'update']);
        Route::delete('presentes/{presente}', [V1\PresenteController::class, 'destroy']);
        Route::post('presentes/{presente}/reserva', [V1\PresenteController::class, 'reservar']);
        Route::delete('presentes/{presente}/reserva', [V1\PresenteController::class, 'liberar']);

        Route::get('grupos', [V1\GrupoController::class, 'index']);
        Route::post('grupos', [V1\GrupoController::class, 'store']);
        Route::post('grupos/entrar/{token}', [V1\GrupoController::class, 'entrar']);
        Route::get('grupos/{grupo}', [V1\GrupoController::class, 'show']);
        Route::match(['put', 'patch'], 'grupos/{grupo}', [V1\GrupoController::class, 'update']);
        Route::post('grupos/{grupo}/atualizar', [V1\GrupoController::class, 'update']);
        Route::delete('grupos/{grupo}', [V1\GrupoController::class, 'destroy']);
        Route::post('grupos/{grupo}/sair', [V1\GrupoController::class, 'sair']);
        Route::post('grupos/{grupo}/convite', [V1\GrupoController::class, 'convite']);
        Route::post('grupos/{grupo}/convidar-amigos', [V1\GrupoController::class, 'convidarAmigos']);
        Route::post('grupos/{grupo}/membros/{membro}/promover', [V1\GrupoController::class, 'promover']);
        Route::delete('grupos/{grupo}/membros/{membro}', [V1\GrupoController::class, 'removerMembro']);

        Route::get('amigos', [V1\AmizadeController::class, 'index']);
        Route::get('amigos/pendentes', [V1\AmizadeController::class, 'pendentes']);
        Route::get('usuarios/buscar', [V1\AmizadeController::class, 'buscar']);
        Route::post('amigos/{usuario}/solicitacao', [V1\AmizadeController::class, 'solicitar']);
        Route::post('amigos/{usuario}/aceitar', [V1\AmizadeController::class, 'aceitar']);
        Route::post('amigos/{usuario}/recusar', [V1\AmizadeController::class, 'recusar']);
        Route::delete('amigos/{usuario}/solicitacao', [V1\AmizadeController::class, 'cancelar']);
        Route::delete('amigos/{usuario}', [V1\AmizadeController::class, 'desfazer']);
        Route::post('amigos/{usuario}/bloqueio', [V1\AmizadeController::class, 'bloquear']);
        Route::delete('amigos/{usuario}/bloqueio', [V1\AmizadeController::class, 'desbloquear']);

        Route::get('notificacoes', [V1\NotificacaoController::class, 'index']);
        Route::get('notificacoes/nao-lidas', [V1\NotificacaoController::class, 'naoLidas']);
        Route::post('notificacoes/ler-todas', [V1\NotificacaoController::class, 'marcarTodasLidas']);
        Route::post('notificacoes/{notificacao}/lida', [V1\NotificacaoController::class, 'marcarLida']);

        Route::post('dispositivos', [V1\DispositivoController::class, 'store']);
        Route::delete('dispositivos', [V1\DispositivoController::class, 'destroy']);

        Route::get('categorias', [V1\CategoriaController::class, 'index']);
        Route::post('categorias', [V1\CategoriaController::class, 'store']);
        Route::post('categorias/ordem', [V1\CategoriaController::class, 'reordenar']);
        Route::match(['put', 'patch'], 'categorias/{categoria}', [V1\CategoriaController::class, 'update']);
        Route::delete('categorias/{categoria}', [V1\CategoriaController::class, 'destroy']);
    });
});
