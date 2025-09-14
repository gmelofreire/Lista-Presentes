<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListaStoreValidator;
use App\Http\Requests\ListaUpdateValidator;
use App\Models\Categoria;
use App\Models\Lista;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ListaController extends Controller
{
    protected $title = 'Listas de Presentes';

    public function index(Request $request)
    {
        $query = Lista::whereHas('usuarios', function ($q) {
            $q->where('usuario_id', auth()->user()->id);
        });

        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nome', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('descricao', 'LIKE', "%{$searchTerm}%");
            });
        }

        $perPage = $request->get('per_page', 15);
        $listas = $query->paginate($perPage);

        return Inertia::render('Lista/Index', [
            'title' => $this->title,
            'listas' => $listas,
            'filters' => [
                'search' => $request->search ?? '',
            ]
        ]);
    }

    public function show($id)
    {
        $lista = Lista::with(['presentes.categorias'])->find($id);
        $categorias = Categoria::where("cadastrado_por", auth()->user()->id)->get();

        return Inertia::render('Lista/Show', [
            'title' => $this->title,
            'lista' => $lista,
            'categorias' => $categorias,
        ]);
    }

    public function create(Request $request)
    {
        $grupos = auth()->user()->grupos;
        return Inertia::render('Lista/Create', [
            'title' => $this->title,
            'grupos' => $grupos,
            'grupo_id' => $request->get('grupo_id', ''),
        ]);
    }

    public function store(ListaStoreValidator $request, FileUploadService $uploader)
    {
        try {
            $dados = $request->validated();

            if (isset($dados['image_url']) && $dados['image_url'] instanceof UploadedFile) {
                $dados['image_url'] = $uploader->upload($dados['image_url'], "listas", $uploader->extensoesImagem);
            } else {
                $dados['image_url'] = "/img/default_cover.png";
            }
            $lista = Lista::create($dados);
            $lista->usuarios()->attach(auth()->user()->id);
            return Redirect::route('listas.index')->with('status', 'Lista criada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }

    }

    public function edit($id)
    {
        $lista = Lista::find($id);
        $grupos = auth()->user()->grupos;
        return Inertia::render('Lista/Edit', [
            'title' => $this->title,
            'grupos' => $grupos,
            'lista' => $lista
        ]);
    }

    public function update($id, Request $request, FileUploadService $uploader)
    {
        try {
            $dados = $request->all();
            if ($dados['image_url'] instanceof UploadedFile) {
                $dados['image_url'] = $uploader->upload($dados['image_url'], 'listas', $uploader->extensoesImagem);
            } elseif (($dados['image_url']) == null || $dados['image_url'] == "") {
                $dados['image_url'] = "/img/default_cover.png";
            }
            Lista::find($id)->update($dados);
            return Redirect::route('listas.index')->with('status', 'Lista atualizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
