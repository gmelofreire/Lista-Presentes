<?php

namespace App\Http\Controllers;

use App\Http\Requests\PresenteUpdateValidator;
use App\Http\Requests\PresenteValidator;
use App\Models\Presente;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PresenteController extends Controller
{
    protected $title = 'Presentes';
    public function create($lista_id)
    {
        return Inertia::render('Presente/Create', [
            'title' => $this->title,
            'lista_id' => $lista_id,
        ]);
    }

    public function store(PresenteValidator $request, FileUploadService $uploader){
        $dados = $request->validated();
        if (isset($dados['image_url']) && $dados['image_url'] instanceof \Illuminate\Http\UploadedFile) {
            $dados['image_url'] = $uploader->upload($dados['image_url'], 'presente', $uploader->extensoesImagem);
        }
        Presente::create($dados);
        return redirect()->route('listas.show', $dados['lista_id']);
    }

    public function edit($id)
    {
        $presente = Presente::find($id);
        return Inertia::render('Presente/Edit', [
            'title' => $this->title,
            'presente' => $presente,
        ]);
    }

    public function update(PresenteUpdateValidator $request, FileUploadService $uploader, $id)
    {
        $dados = $request->validated();
        if (isset($dados['image_url']) && $dados['image_url'] instanceof \Illuminate\Http\UploadedFile) {
            $dados['image_url'] = $uploader->upload($dados['image_url'], 'presente', $uploader->extensoesImagem);
        }
        $presente = Presente::find($id);
        $presente->update($dados);
        return redirect()->route('listas.show', $presente->lista_id);
    }

    public function destroy($id)
    {
        $presente = Presente::find($id);
        $lista_id = $presente->lista_id;
        $presente->delete();
        return redirect()->route('listas.show', $lista_id);
    }
}
