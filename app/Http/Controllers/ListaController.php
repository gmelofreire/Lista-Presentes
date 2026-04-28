<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListaStoreValidator;
use App\Models\Categoria;
use App\Models\Lista;
use App\Models\ListaParticipante;
use App\Models\User;
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
        $query = Lista::with('grupo')->whereHas('usuarios', function ($q) {
            $q->where('usuario_id', auth()->user()->id);
        });

        if ($request->has('search') && ! empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('nome', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('descricao', 'LIKE', "%{$searchTerm}%");
            });
        }

        if ($request->has('status') && ! empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('grupo_id') && ! empty($request->grupo_id)) {
            $query->where('grupo_id', $request->grupo_id);
        }

        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');

        $allowedSorts = ['nome', 'data_evento', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        }

        $perPage = $request->get('per_page', 15);
        $listas = $query->paginate($perPage);

        $grupos = auth()->user()->grupos;

        return Inertia::render('Lista/Index', [
            'title' => $this->title,
            'listas' => $listas,
            'filters' => [
                'search' => $request->search ?? '',
                'status' => $request->status ?? '',
                'grupo_id' => $request->grupo_id ?? '',
                'sort' => $sortBy,
                'direction' => $sortDir,
            ],
            'grupos' => $grupos,
        ]);
    }

    public function show($id)
    {
        $lista = Lista::with(['presentes.categorias', 'participantes.usuario.perfil'])->find($id);
        $categorias = Categoria::where('cadastrado_por', auth()->user()->id)->get();

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
                $dados['image_url'] = $uploader->upload($dados['image_url'], 'listas', $uploader->extensoesImagem);
            } else {
                $dados['image_url'] = '/img/default_cover.png';
            }
            $lista = Lista::create($dados);
            $lista->usuarios()->attach(auth()->user()->id);

            ListaParticipante::create([
                'lista_id' => $lista->id,
                'usuario_id' => auth()->user()->id,
                'papel' => 'admin',
            ]);

            // Dispara notificação aos membros do grupo
            if ($lista->grupo_id) {
                $grupo = \App\Models\Grupo::find($lista->grupo_id);
                if ($grupo) {
                    event(new \App\Events\NovaListaNoGrupo($lista, $grupo, auth()->user()));
                }
            }

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
            'lista' => $lista,
        ]);
    }

    public function update($id, Request $request, FileUploadService $uploader)
    {
        try {
            $dados = $request->all();
            if ($dados['image_url'] instanceof UploadedFile) {
                $dados['image_url'] = $uploader->upload($dados['image_url'], 'listas', $uploader->extensoesImagem);
            } elseif (($dados['image_url']) == null || $dados['image_url'] == '') {
                $dados['image_url'] = '/img/default_cover.png';
            }
            Lista::find($id)->update($dados);

            return Redirect::route('listas.index')->with('status', 'Lista atualizada com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function buscarParticipantes(Request $request)
    {
        $termo = $request->get('termo', '');

        $usuarios = User::where(function ($query) use ($termo) {
            $query->where('name', 'LIKE', "%{$termo}%")
                ->orWhere('email', 'LIKE', "%{$termo}%")
                ->orWhere('username', 'LIKE', "%{$termo}%");
        })
            ->where('id', '!=', auth()->user()->id)
            ->limit(10)
            ->get(['id', 'name', 'username', 'email']);

        return response()->json($usuarios);
    }

    public function adicionarParticipante(Request $request, $listaId)
    {
        $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'papel' => 'required|in:admin,editor,visualizador',
        ]);

        $lista = Lista::findOrFail($listaId);

        $existe = ListaParticipante::where('lista_id', $listaId)
            ->where('usuario_id', $request->usuario_id)
            ->first();

        if ($existe) {
            return redirect()->back()->withErrors(['error' => 'Usuário já é participante desta lista.']);
        }

        ListaParticipante::create([
            'lista_id' => $listaId,
            'usuario_id' => $request->usuario_id,
            'papel' => $request->papel,
        ]);

        $lista->usuarios()->attach($request->usuario_id);

        return redirect()->back()->with('status', 'Participante adicionado com sucesso!');
    }

    public function removerParticipante($listaId, $participanteId)
    {
        $participante = ListaParticipante::findOrFail($participanteId);

        if ($participante->papel === 'admin' && ListaParticipante::where('lista_id', $listaId)->where('papel', 'admin')->count() === 1) {
            return redirect()->back()->withErrors(['error' => 'Não é possível remover o último administrador.']);
        }

        $participante->delete();
        $participante->lista->usuarios()->detach($participante->usuario_id);

        return redirect()->back()->with('status', 'Participante removido com sucesso!');
    }

    public function atualizarPapel(Request $request, $participanteId)
    {
        $request->validate([
            'papel' => 'required|in:admin,editor,visualizador',
        ]);

        $participante = ListaParticipante::findOrFail($participanteId);

        if ($participante->papel === 'admin' && $request->papel !== 'admin') {
            $outrosAdmins = ListaParticipante::where('lista_id', $participante->lista_id)
                ->where('papel', 'admin')
                ->where('id', '!=', $participanteId)
                ->count();

            if ($outrosAdmins === 0) {
                return redirect()->back()->withErrors(['error' => 'A lista deve ter pelo menos um administrador.']);
            }
        }

        $participante->update(['papel' => $request->papel]);

        return redirect()->back()->with('status', 'Permissão atualizada com sucesso!');
    }

    public function gerarLinkConvite($listaId)
    {
        $lista = Lista::findOrFail($listaId);
        $token = \Illuminate\Support\Str::random(32);

        $lista->update(['token_convite' => $token]);

        return response()->json([
            'link' => route('listas.participar', ['listaId' => $listaId, 'token' => $token]),
            'token' => $token,
        ]);
    }

    public function participar(Request $request, $listaId)
    {
        $lista = Lista::where('id', $listaId)->firstOrFail();

        if ($lista->token_convite && $lista->token_convite !== $request->token) {
            abort(403, 'Link de convite inválido.');
        }

        if (! $lista->usuarios()->where('usuario_id', auth()->user()->id)->exists()) {
            $papel = $request->get('papel', 'visualizador');

            ListaParticipante::create([
                'lista_id' => $listaId,
                'usuario_id' => auth()->user()->id,
                'papel' => $papel,
            ]);

            $lista->usuarios()->attach(auth()->user()->id);
        }

        return Redirect::route('listas.show', $listaId);
    }
}
