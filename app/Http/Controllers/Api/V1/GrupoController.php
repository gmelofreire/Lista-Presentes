<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\GrupoValidator;
use App\Http\Resources\GrupoResource;
use App\Models\Grupo;
use App\Services\GrupoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Grupos
 */
class GrupoController extends Controller
{
    public function __construct(private GrupoService $grupos) {}

    public function index(Request $request)
    {
        $request->validate(['search' => 'nullable|string|max:100', 'per_page' => 'nullable|integer|min:1|max:50']);

        $query = $request->user()->grupos()->withCount('integrantes')
            ->when($request->filled('search'), fn ($q) => $q->where(fn ($w) => $w
                ->where('nome', 'LIKE', '%'.$request->search.'%')
                ->orWhere('descricao', 'LIKE', '%'.$request->search.'%')))
            ->orderBy('grupos.created_at', 'desc')->orderBy('grupos.id');

        return GrupoResource::collection($query->cursorPaginate((int) $request->get('per_page', 15)));
    }

    public function show(string $grupo): GrupoResource
    {
        $grupo = Grupo::findOrFail($grupo);
        $this->authorize('view', $grupo);

        return GrupoResource::make($grupo->load(['integrantes.perfil', 'listas'])->loadCount('integrantes'));
    }

    /**
     * Cria o grupo; `integrante_ids` aceita somente amigos.
     */
    public function store(GrupoValidator $request): JsonResponse
    {
        $grupo = $this->grupos->criar($request->user(), $request->validated());

        return GrupoResource::make($grupo->load('integrantes'))->response()->setStatusCode(201);
    }

    public function update(GrupoValidator $request, string $grupo): GrupoResource
    {
        $grupo = Grupo::findOrFail($grupo);
        $this->authorize('update', $grupo);

        return GrupoResource::make($this->grupos->atualizar($grupo, $request->user(), $request->validated())->load('integrantes'));
    }

    public function destroy(string $grupo): JsonResponse
    {
        $grupo = Grupo::findOrFail($grupo);
        $this->authorize('delete', $grupo);

        $this->grupos->excluir($grupo);

        return response()->json(null, 204);
    }

    public function sair(string $grupo): JsonResponse
    {
        $this->grupos->sair(Grupo::findOrFail($grupo), auth()->user());

        return response()->json(null, 204);
    }

    public function removerMembro(string $grupo, string $membro): JsonResponse
    {
        $grupo = Grupo::findOrFail($grupo);
        $this->authorize('manage', $grupo);

        $this->grupos->removerMembro($grupo, auth()->user(), $membro);

        return response()->json(null, 204);
    }

    /**
     * Promove um membro a admin (somente o proprietário).
     */
    public function promover(string $grupo, string $membro): JsonResponse
    {
        $grupo = Grupo::findOrFail($grupo);
        $this->authorize('promote', $grupo);

        $this->grupos->promoverAdmin($grupo, $membro);

        return response()->json(['message' => 'Administrador atribuído.']);
    }

    public function convidarAmigos(Request $request, string $grupo): JsonResponse
    {
        $grupo = Grupo::findOrFail($grupo);
        $this->authorize('manage', $grupo);

        $ids = $request->validate([
            'friend_ids' => 'required|array|min:1',
            'friend_ids.*' => 'string|exists:users,id',
        ])['friend_ids'];

        return response()->json($this->grupos->convidarAmigos($grupo, auth()->user(), $ids));
    }

    public function convite(string $grupo): JsonResponse
    {
        $grupo = Grupo::findOrFail($grupo);
        $this->authorize('manage', $grupo);

        $token = $this->grupos->gerarConvite($grupo);

        return response()->json(['token' => $token, 'expira_em' => $grupo->fresh()->token_expira_em]);
    }

    /**
     * Entra no grupo com o token. `resultado`: entrou, ja_membro ou pendente (grupo privado).
     */
    public function entrar(string $token): JsonResponse
    {
        [$grupo, $resultado] = $this->grupos->entrarPorConvite($token, auth()->user());

        return response()->json(['resultado' => $resultado, 'grupo_id' => $grupo->id]);
    }
}
