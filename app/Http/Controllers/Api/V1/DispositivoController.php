<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Dispositivo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Dispositivos (push)
 */
class DispositivoController extends Controller
{
    /**
     * Registra (ou atualiza) o token de push do aparelho. Se o token já era de outro usuário, passa para o atual.
     */
    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'token' => 'required|string|max:512',
            'plataforma' => 'required|in:ios,android',
            'nome' => 'nullable|string|max:100',
        ]);

        $dispositivo = Dispositivo::updateOrCreate(
            ['token' => $dados['token']],
            ['user_id' => $request->user()->id, 'plataforma' => $dados['plataforma'], 'nome' => $dados['nome'] ?? null, 'ultimo_uso_em' => now()],
        );

        return response()->json(['id' => $dispositivo->id], 201);
    }

    /**
     * Remove o token (chame no logout do app).
     */
    public function destroy(Request $request): JsonResponse
    {
        $token = $request->validate(['token' => 'required|string'])['token'];

        $request->user()->dispositivos()->where('token', $token)->delete();

        return response()->json(null, 204);
    }
}
