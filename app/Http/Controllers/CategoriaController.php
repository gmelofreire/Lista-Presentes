<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoriaValidator;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoriaController extends Controller
{
    protected $title = 'Categorias';

    public function index()
    {
        $categorias = Categoria::where("cadastrado_por", auth()->user()->id)->get();

        return Inertia::render("Categoria/Index", [
            "categorias" => $categorias,
            "title" => $this->title,
        ]);
    }

    public function create()
    {
        return Inertia::render("Categoria/Create", [
            "title" => $this->title,
        ]);
    }

    public function store(CategoriaValidator $request)
    {
        try {
            $dados = $request->validated();
            Categoria::create($dados);
            return redirect()->route("categorias.index")->with('status', 'Lista criada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit(Categoria $categoria)
    {
        if ($categoria->cadastrado_por != auth()->user()->id) {
            return redirect()->back()->withErrors(['error' => 'Categoria não encontrada']);
        }
        return Inertia::render("Categoria/Edit", [
            "title" => $this->title,
            "categoria" => $categoria,
        ]);
    }

    public function update(CategoriaValidator $request, $id){
        try {
            $dados = $request->validated();
            $categoria = Categoria::findOrFail($id);
            $categoria->update($dados);
            return redirect()->route("categorias.index")->with('status', 'Categoria atualizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
