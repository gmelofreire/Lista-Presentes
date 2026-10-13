<?php

namespace App\Http\Controllers;

use App\Http\Requests\PresenteUpdateValidator;
use App\Http\Requests\PresenteValidator;
use App\Models\Categoria;
use App\Models\Lista;
use App\Models\Presente;
use App\Services\PresenteService;
use App\Services\ProdutoUrlScraper;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PresenteController extends Controller
{
    protected $title = 'Presentes';

    public function __construct(private PresenteService $presentes) {}

    public function create($lista_id)
    {
        $this->authorize('update', Lista::findOrFail($lista_id));
        $categorias = Categoria::where('cadastrado_por', auth()->user()->id)->get();

        return Inertia::render('Presente/Create', [
            'title' => $this->title,
            'categorias' => $categorias,
            'lista_id' => $lista_id,
        ]);
    }

    public function store(PresenteValidator $request)
    {
        $this->authorize('update', Lista::findOrFail($request->input('lista_id')));

        $presente = $this->presentes->criar($request->user(), $request->validated());

        return redirect()->route('listas.show', $presente->lista_id)->with('status', 'Presente adicionado à lista.');
    }

    public function edit($id)
    {
        $presente = Presente::with('categorias')->findOrFail($id);
        $this->authorize('update', $presente);
        $presente->categoria_ids = $presente->categorias->pluck('id')->toArray();

        return Inertia::render('Presente/Edit', [
            'title' => $this->title,
            'presente' => $presente,
            'categorias' => Categoria::where('cadastrado_por', auth()->user()->id)->get(),
        ]);
    }

    public function update(PresenteUpdateValidator $request, $id)
    {
        $presente = Presente::findOrFail($id);
        $this->authorize('update', $presente);

        $this->presentes->atualizar($presente, $request->validated());

        return redirect()->route('listas.show', $presente->lista_id)->with('status', 'Presente atualizado.');
    }

    public function destroy($id)
    {
        $presente = Presente::findOrFail($id);
        $this->authorize('delete', $presente);
        $presente->delete();

        return redirect()->route('listas.show', $presente->lista_id)->with('status', 'Presente removido da lista.');
    }

    public function reservar($id)
    {
        $presente = Presente::findOrFail($id);
        $this->authorize('reservar', $presente);

        $this->presentes->reservar($presente, auth()->user());

        return redirect()->back()->with('status', 'Presente reservado!');
    }

    public function liberar($id)
    {
        $presente = Presente::findOrFail($id);
        $this->authorize('reservar', $presente);

        $this->presentes->liberar($presente, auth()->user());

        return redirect()->back()->with('status', 'Reserva cancelada.');
    }

    public function buscarDadosUrl(Request $request, ProdutoUrlScraper $scraper)
    {
        $url = (string) $request->get('url');

        try {
            $resultado = $scraper->buscar($url);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => 'URL inválida'], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['error' => 'Não foi possível buscar os dados desta URL.'], 502);
        }

        if (! $resultado) {
            return response()->json(['error' => 'Não foi possível extrair informações do produto'], 422);
        }

        return response()->json($resultado);
    }
}
