<?php

namespace App\Http\Controllers;

use App\Http\Requests\GrupoValidator;
use App\Services\FileUploadService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;
use App\Models\Grupo;

class GrupoController extends Controller
{
    protected $title = 'Grupos';
    public function index(Request $request)
    {
        $query = auth()->user()->grupos();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                    ->orWhere('descricao', 'like', "%{$search}%");
            });
        }

        $grupos = $query->paginate(9);

        return Inertia::render(
            'Grupo/Index',
            [
                'title' => $this->title,
                'grupos' => $grupos,
                'filters' => [
                    'search' => $request->get('search', '')
                ]
            ]
        );
    }

    public function show($id)
    {
        $grupo = Grupo::with("integrantes", "listas.cadastradoPor")->find($id);
        return Inertia::render(
            'Grupo/Show',
            [
                'title' => $this->title,
                'grupo' => $grupo,
            ]
        );
    }

    public function create()
    {
        return Inertia::render(
            'Grupo/Create',
            [
                'title' => $this->title,
                'amizades' => $this->usuariosAmizades(),
            ]
        );
    }

    public function store(GrupoValidator $request, FileUploadService $uploader)
    {
        try {
            $dados = $request->validated();
            if (isset($dados['image_url']) && $dados['image_url'] instanceof UploadedFile) {
                $dados['image_url'] = $uploader->upload($dados['image_url'], 'grupos', $uploader->extensoesImagem);
            } else {
                $dados['image_url'] = "/img/default_cover.png";
            }
            $grupo = Grupo::create($dados);
            $grupo->integrantes()->attach($dados['integrante_ids']);
            return redirect()->route('grupos.index')->with('success', 'Grupo criado com sucesso!');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit($id){
        $grupo = Grupo::with("integrantes")->find($id);
        
        return Inertia::render(
            'Grupo/Edit',
            [
                'title' => $this->title,
                'grupo' => $grupo,
                'amizades' => $this->usuariosAmizades(),
            ]
        );
    }

    public function update(GrupoValidator $request, $id, FileUploadService $uploader){
        try {
            $grupo = Grupo::find($id);
            $dados = $request->validated();

            if (isset($dados['image_url']) && $dados['image_url'] instanceof UploadedFile) {
                $dados['image_url'] = $uploader->upload($dados['image_url'], 'grupos', $uploader->extensoesImagem);
            } elseif (($dados['image_url']) == null || $dados['image_url'] == "") {
                $dados['image_url'] = "/img/default_cover.png";
            }
            $grupo->update($dados);
            $grupo->integrantes()->detach();
            $grupo->integrantes()->attach($dados['integrante_ids']);
            return redirect()->route('grupos.index')->with('success', 'Grupo atualizado com sucesso!');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    protected function usuariosAmizades(){
        $userId = auth()->id();
        
        $amizadesAceitas = auth()->user()->amizades()->where('status', 'aceito')->get();

        $amigoIds = $amizadesAceitas->map(function ($amizade) use ($userId) {
            return $amizade->usuario_id == $userId ? $amizade->amigo_id : $amizade->usuario_id;
        });

        return \App\Models\User::with("perfil")->whereIn('id', $amigoIds)->get();
    }
}
