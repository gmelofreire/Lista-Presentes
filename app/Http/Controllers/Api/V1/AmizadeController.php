<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Amizade;
use App\Models\User;
use App\Services\AmizadeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Amizades
 */
class AmizadeController extends Controller
{
    public function __construct(private AmizadeService $amizades) {}

    /**
     * Amigos aceitos.
     */
    public function index(Request $request)
    {
        $amigos = User::with('perfil')->whereIn('id', $request->user()->amigoIds())->orderBy('name')->orderBy('id');

        return UserResource::collection($amigos->cursorPaginate(30));
    }

    /**
     * Solicitações recebidas (`recebidas`) e enviadas (`enviadas`) ainda pendentes.
     */
    public function pendentes(Request $request): JsonResponse
    {
        $id = $request->user()->id;

        $recebidas = Amizade::where('amigo_id', $id)->where('status', 'pendente')->with('user.perfil')->get()->pluck('user');
        $enviadas = Amizade::where('usuario_id', $id)->where('status', 'pendente')->with('amigo.perfil')->get()->pluck('amigo');

        return response()->json([
            'recebidas' => UserResource::collection($recebidas),
            'enviadas' => UserResource::collection($enviadas),
        ]);
    }

    /**
     * Busca usuários por nome ou username (mínimo 2 caracteres).
     */
    public function buscar(Request $request)
    {
        $q = (string) $request->query('q', '');
        if (mb_strlen($q) < 2) {
            return UserResource::collection(collect());
        }

        return UserResource::collection(
            User::with('perfil')->where('id', '!=', $request->user()->id)
                ->where(fn ($w) => $w->where('name', 'LIKE', "%{$q}%")->orWhere('username', 'LIKE', "%{$q}%"))
                ->limit(10)->get()
        );
    }

    public function solicitar(Request $request, string $usuario): JsonResponse
    {
        $this->amizades->solicitar($request->user(), User::findOrFail($usuario));

        return response()->json(['message' => 'Solicitação enviada.'], 201);
    }

    public function aceitar(Request $request, string $usuario): JsonResponse
    {
        $this->amizades->aceitar($request->user(), $usuario);

        return response()->json(['message' => 'Amizade aceita.']);
    }

    public function recusar(Request $request, string $usuario): JsonResponse
    {
        $this->amizades->recusar($request->user(), $usuario);

        return response()->json(null, 204);
    }

    public function cancelar(Request $request, string $usuario): JsonResponse
    {
        $this->amizades->cancelar($request->user(), $usuario);

        return response()->json(null, 204);
    }

    public function desfazer(Request $request, string $usuario): JsonResponse
    {
        $this->amizades->desfazer($request->user(), $usuario);

        return response()->json(null, 204);
    }

    public function bloquear(Request $request, string $usuario): JsonResponse
    {
        $this->amizades->bloquear($request->user(), $usuario);

        return response()->json(['message' => 'Usuário bloqueado.']);
    }

    public function desbloquear(Request $request, string $usuario): JsonResponse
    {
        $this->amizades->desbloquear($request->user(), $usuario);

        return response()->json(null, 204);
    }
}
