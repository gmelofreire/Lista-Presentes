<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificacaoResource;
use App\Models\Notificacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Notificações
 */
class NotificacaoController extends Controller
{
    public function index(Request $request)
    {
        $query = Notificacao::where('user_id', $request->user()->id)
            ->when($request->boolean('somente_nao_lidas'), fn ($q) => $q->where('lido', false))
            ->orderByDesc('created_at')->orderByDesc('id');

        return NotificacaoResource::collection($query->cursorPaginate(30));
    }

    public function naoLidas(Request $request): JsonResponse
    {
        return response()->json([
            'count' => Notificacao::where('user_id', $request->user()->id)->where('lido', false)->count(),
        ]);
    }

    public function marcarLida(Request $request, string $notificacao): JsonResponse
    {
        Notificacao::where('user_id', $request->user()->id)->whereKey($notificacao)->firstOrFail()
            ->update(['lido' => true, 'data_lido' => now()]);

        return response()->json(null, 204);
    }

    public function marcarTodasLidas(Request $request): JsonResponse
    {
        Notificacao::where('user_id', $request->user()->id)->where('lido', false)
            ->update(['lido' => true, 'data_lido' => now()]);

        return response()->json(null, 204);
    }
}
