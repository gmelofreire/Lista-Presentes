<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoriaValidator;
use App\Http\Resources\CategoriaResource;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @tags Categorias
 */
class CategoriaController extends Controller
{
    public function index(Request $request)
    {
        return CategoriaResource::collection(
            Categoria::where('cadastrado_por', $request->user()->id)->orderBy('ordem')->orderBy('nome')->get()
        );
    }

    public function store(CategoriaValidator $request): JsonResponse
    {
        return CategoriaResource::make(Categoria::create($request->validated()))->response()->setStatusCode(201);
    }

    public function update(CategoriaValidator $request, string $categoria): CategoriaResource
    {
        $categoria = Categoria::findOrFail($categoria);
        $this->authorize('update', $categoria);

        $dados = $request->validated();
        unset($dados['cadastrado_por']);
        $categoria->update($dados);

        return CategoriaResource::make($categoria);
    }

    public function destroy(string $categoria): JsonResponse
    {
        $categoria = Categoria::findOrFail($categoria);
        $this->authorize('delete', $categoria);

        DB::transaction(function () use ($categoria) {
            $categoria->presentes()->detach();
            Categoria::where('parent_id', $categoria->id)->update(['parent_id' => null]);
            $categoria->delete();
        });

        return response()->json(null, 204);
    }

    /**
     * Define a ordem das categorias do usuário: `order` é a lista de ids na ordem desejada.
     */
    public function reordenar(Request $request): JsonResponse
    {
        $ids = $request->validate(['order' => 'required|array', 'order.*' => 'string'])['order'];

        DB::transaction(function () use ($ids, $request) {
            foreach ($ids as $i => $id) {
                Categoria::where('cadastrado_por', $request->user()->id)->whereKey($id)->update(['ordem' => $i]);
            }
        });

        return response()->json(null, 204);
    }
}
