<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListaStoreValidator;
use App\Http\Requests\ListaUpdateValidator;
use App\Http\Resources\ListaResource;
use App\Models\Lista;
use App\Services\ListaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Listas
 */
class ListaController extends Controller
{
    public function __construct(private ListaService $listas) {}

    /**
     * Listas em que o usuário participa. Paginação por cursor (`next_cursor`).
     */
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:100',
            'status' => 'nullable|in:ativa,inativa',
            'grupo_id' => 'nullable|string',
            'sort' => 'nullable|in:nome,data_evento,created_at',
            'direction' => 'nullable|in:asc,desc',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $query = Lista::query()
            ->whereHas('participantes', fn ($q) => $q->where('usuario_id', $request->user()->id))
            ->with(['participantes' => fn ($q) => $q->where('usuario_id', $request->user()->id)])
            ->withCount('presentes');

        $query->when($request->filled('search'), fn ($q) => $q->where(fn ($w) => $w
            ->where('nome', 'LIKE', '%'.$request->search.'%')
            ->orWhere('descricao', 'LIKE', '%'.$request->search.'%')));
        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->status));
        $query->when($request->filled('grupo_id'), fn ($q) => $q->where('grupo_id', $request->grupo_id));

        // `id` desempata para o cursor ser estável.
        $query->orderBy($request->get('sort', 'created_at'), $request->get('direction', 'desc'))->orderBy('id');

        return ListaResource::collection($query->cursorPaginate((int) $request->get('per_page', 15)));
    }

    /**
     * Detalhe da lista com presentes e participantes.
     */
    public function show(string $lista): ListaResource
    {
        $lista = Lista::with(['presentes.categorias', 'participantes.usuario.perfil', 'grupo'])->findOrFail($lista);
        $this->authorize('view', $lista);

        return ListaResource::make($lista);
    }

    /**
     * Cria uma lista (multipart quando enviar `image_url`).
     */
    public function store(ListaStoreValidator $request): JsonResponse
    {
        $lista = $this->listas->criar($request->user(), $request->validated());

        return ListaResource::make($lista->load('participantes'))->response()->setStatusCode(201);
    }

    /**
     * Atualiza a lista. Com upload de imagem use POST com `_method=PATCH`.
     */
    public function update(ListaUpdateValidator $request, string $lista): ListaResource
    {
        $lista = Lista::findOrFail($lista);
        $this->authorize('update', $lista);

        return ListaResource::make($this->listas->atualizar($lista, $request->user(), $request->validated())->load('participantes'));
    }

    public function destroy(string $lista): JsonResponse
    {
        $lista = Lista::findOrFail($lista);
        $this->authorize('delete', $lista);

        $this->listas->excluir($lista);

        return response()->json(null, 204);
    }

    /**
     * Gera link/token de convite (somente admin da lista).
     */
    public function convite(string $lista): JsonResponse
    {
        $lista = Lista::findOrFail($lista);
        $this->authorize('manage', $lista);

        $token = $this->listas->gerarConvite($lista)['token'];

        return response()->json(['token' => $token, 'expira_em' => $lista->fresh()->token_expira_em]);
    }

    /**
     * Entra na lista com um token de convite (sempre como visualizador).
     */
    public function entrar(Request $request, string $lista): ListaResource
    {
        $dados = $request->validate(['token' => 'required|string']);
        $lista = Lista::findOrFail($lista);

        $this->listas->entrarPorConvite($lista, $request->user(), $dados['token']);

        return ListaResource::make($lista->load('participantes'));
    }

    public function adicionarParticipante(Request $request, string $lista): JsonResponse
    {
        $lista = Lista::findOrFail($lista);
        $this->authorize('manage', $lista);

        $dados = $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'papel' => 'required|in:admin,editor,visualizador',
        ]);

        $this->listas->adicionarParticipante($lista, $dados['usuario_id'], $dados['papel'], $request->user());

        return response()->json(['message' => 'Participante adicionado.'], 201);
    }

    public function alterarPapel(Request $request, string $lista, string $participante): JsonResponse
    {
        $lista = Lista::findOrFail($lista);
        $this->authorize('manage', $lista);

        $dados = $request->validate(['papel' => 'required|in:admin,editor,visualizador']);
        $p = $this->listas->alterarPapel($lista, $participante, $dados['papel']);

        return response()->json(['id' => $p->id, 'papel' => $p->papel]);
    }

    public function removerParticipante(string $lista, string $participante): JsonResponse
    {
        $lista = Lista::findOrFail($lista);
        $this->authorize('manage', $lista);

        $this->listas->removerParticipante($lista, $participante);

        return response()->json(null, 204);
    }
}
