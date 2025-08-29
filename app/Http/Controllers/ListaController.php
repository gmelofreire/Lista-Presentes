<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListaStoreValidator;
use App\Models\Lista;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ListaController extends Controller
{
    protected $title = 'Listas de Presentes';

    public function index(Request $request)
    {
        $query = Lista::where("cadastrado_por_id", auth()->user()->id);

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

    public function create()
    {
        return Inertia::render('Lista/Create', [
            'title' => $this->title,
        ]);
    }

    public function store(ListaStoreValidator $request, FileUploadService $uploader)
    {
        try {
            $dados = $request->validated();
            $dados['image_url'] = $uploader->upload($dados["image_url"], "listas", $uploader->extensoesImagem);
            $lista = Lista::create($dados);
            return Redirect::route('listas.index')->with('status', 'Lista criada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }

    }
}
