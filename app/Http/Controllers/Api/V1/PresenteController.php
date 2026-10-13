<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PresenteUpdateValidator;
use App\Http\Requests\PresenteValidator;
use App\Http\Resources\PresenteResource;
use App\Models\Lista;
use App\Models\Presente;
use App\Services\PresenteService;
use App\Services\ProdutoUrlScraper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @tags Presentes
 */
class PresenteController extends Controller
{
    public function __construct(private PresenteService $presentes) {}

    /**
     * Presentes da lista.
     */
    public function index(string $lista)
    {
        $lista = Lista::findOrFail($lista);
        $this->authorize('view', $lista);

        return PresenteResource::collection($lista->presentes()->with('categorias')->orderBy('created_at')->orderBy('id')->cursorPaginate(30));
    }

    /**
     * Adiciona presente (admin ou editor da lista).
     */
    public function store(PresenteValidator $request, string $lista): JsonResponse
    {
        $this->authorize('update', Lista::findOrFail($lista));

        $presente = $this->presentes->criar($request->user(), $request->validated());

        return PresenteResource::make($presente->load('categorias'))->response()->setStatusCode(201);
    }

    public function show(string $presente): PresenteResource
    {
        $presente = Presente::with('categorias')->findOrFail($presente);
        $this->authorize('view', $presente);

        return PresenteResource::make($presente);
    }

    public function update(PresenteUpdateValidator $request, string $presente): PresenteResource
    {
        $presente = Presente::findOrFail($presente);
        $this->authorize('update', $presente);

        return PresenteResource::make($this->presentes->atualizar($presente, $request->validated())->load('categorias'));
    }

    public function destroy(string $presente): JsonResponse
    {
        $presente = Presente::findOrFail($presente);
        $this->authorize('delete', $presente);
        $presente->delete();

        return response()->json(null, 204);
    }

    /**
     * Reserva o presente. O dono da lista não pode reservar, e dois pedidos simultâneos resultam em 409 para o segundo.
     */
    public function reservar(string $presente): PresenteResource
    {
        $presente = Presente::findOrFail($presente);
        $this->authorize('reservar', $presente);

        $this->presentes->reservar($presente, auth()->user());

        return PresenteResource::make($presente->refresh());
    }

    /**
     * Cancela a reserva (somente quem reservou).
     */
    public function liberar(string $presente): PresenteResource
    {
        $presente = Presente::findOrFail($presente);
        $this->authorize('reservar', $presente);

        $this->presentes->liberar($presente, auth()->user());

        return PresenteResource::make($presente->refresh());
    }

    /**
     * Pré-preenche dados de um produto a partir da URL da loja.
     */
    public function buscarDadosUrl(Request $request, ProdutoUrlScraper $scraper): JsonResponse
    {
        $request->validate(['url' => 'required|string|max:2048']);

        try {
            $resultado = $scraper->buscar($request->input('url'));
        } catch (\InvalidArgumentException) {
            return response()->json(['message' => 'URL inválida.'], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Não foi possível buscar os dados desta URL.'], 502);
        }

        return $resultado
            ? response()->json($resultado)
            : response()->json(['message' => 'Não foi possível extrair informações do produto.'], 422);
    }
}
