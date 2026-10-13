<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListaStoreValidator;
use App\Http\Requests\ListaUpdateValidator;
use App\Models\Categoria;
use App\Models\Lista;
use App\Models\User;
use App\Services\ListaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ListaController extends Controller
{
    protected $title = 'Listas de Presentes';

    public function __construct(private ListaService $listas) {}

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
        $sortDir = $request->get('direction') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['nome', 'data_evento', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        }

        $perPage = min(max((int) $request->get('per_page', 15), 1), 50);
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
        $lista = Lista::with(['presentes.categorias', 'participantes.usuario.perfil'])->findOrFail($id);
        $this->authorize('view', $lista);
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

    public function store(ListaStoreValidator $request)
    {
        $this->listas->criar($request->user(), $request->validated());

        return Redirect::route('listas.index')->with('status', 'Lista criada com sucesso!');
    }

    public function edit($id)
    {
        $lista = Lista::findOrFail($id);
        $this->authorize('update', $lista);

        return Inertia::render('Lista/Edit', [
            'title' => $this->title,
            'grupos' => auth()->user()->grupos,
            'lista' => $lista,
        ]);
    }

    public function update($id, ListaUpdateValidator $request)
    {
        $lista = Lista::findOrFail($id);
        $this->authorize('update', $lista);

        $this->listas->atualizar($lista, $request->user(), $request->validated());

        return Redirect::route('listas.index')->with('status', 'Lista atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $lista = Lista::findOrFail($id);
        $this->authorize('delete', $lista);

        $this->listas->excluir($lista);

        return Redirect::route('listas.index')->with('status', 'Lista excluída.');
    }

    public function buscarParticipantes(Request $request, $listaId)
    {
        $this->authorize('manage', Lista::findOrFail($listaId));

        $termo = (string) $request->get('termo', '');
        if (mb_strlen($termo) < 2) {
            return response()->json([]);
        }

        return response()->json(
            User::where(fn ($q) => $q->where('name', 'LIKE', "%{$termo}%")->orWhere('username', 'LIKE', "%{$termo}%"))
                ->where('id', '!=', auth()->id())
                ->limit(10)
                ->get(['id', 'name', 'username'])
        );
    }

    public function adicionarParticipante(Request $request, $listaId)
    {
        $lista = Lista::findOrFail($listaId);
        $this->authorize('manage', $lista);

        $dados = $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'papel' => 'required|in:admin,editor,visualizador',
        ]);

        $this->listas->adicionarParticipante($lista, $dados['usuario_id'], $dados['papel'], $request->user());

        return redirect()->back()->with('status', 'Participante adicionado com sucesso!');
    }

    public function removerParticipante($listaId, $participanteId)
    {
        $lista = Lista::findOrFail($listaId);
        $this->authorize('manage', $lista);

        $this->listas->removerParticipante($lista, $participanteId);

        return redirect()->back()->with('status', 'Participante removido com sucesso!');
    }

    public function atualizarPapel(Request $request, $listaId, $participanteId)
    {
        $lista = Lista::findOrFail($listaId);
        $this->authorize('manage', $lista);

        $dados = $request->validate(['papel' => 'required|in:admin,editor,visualizador']);

        $this->listas->alterarPapel($lista, $participanteId, $dados['papel']);

        return redirect()->back()->with('status', 'Permissão atualizada com sucesso!');
    }

    public function gerarLinkConvite($listaId)
    {
        $lista = Lista::findOrFail($listaId);
        $this->authorize('manage', $lista);

        $token = $this->listas->gerarConvite($lista)['token'];

        return response()->json([
            'link' => route('listas.participar', ['listaId' => $listaId, 'token' => $token]),
            'token' => $token,
        ]);
    }

    public function participar(Request $request, $listaId)
    {
        $lista = Lista::findOrFail($listaId);

        $this->listas->entrarPorConvite($lista, $request->user(), (string) $request->query('token', ''));

        return Redirect::route('listas.show', $listaId);
    }
}
