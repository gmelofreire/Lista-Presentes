<?php

namespace App\Http\Controllers;

use App\Http\Requests\GrupoValidator;
use App\Models\Grupo;
use App\Services\GrupoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GrupoController extends Controller
{
    protected $title = 'Grupos';

    public function __construct(private GrupoService $grupos) {}

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

        return Inertia::render('Grupo/Index', [
            'title' => $this->title,
            'grupos' => $grupos,
            'filters' => ['search' => $request->get('search', '')],
        ]);
    }

    public function show($id)
    {
        $this->authorize('view', Grupo::findOrFail($id));

        $grupo = Grupo::with(['integrantes' => function ($q) {
            $q->select('users.id', 'name', 'email', 'username')->with('perfil');
        }, 'listas.cadastradoPor.perfil'])->findOrFail($id);

        $grupo->integrantes->each(function ($membro) {
            $membro->pivot = ['role' => $membro->pivot->role ?? 'visualizador'];
        });

        return Inertia::render('Grupo/Show', [
            'title' => $this->title,
            'grupo' => $grupo,
            'amigos' => $this->getAvailableFriends($grupo->id),
        ]);
    }

    public function create()
    {
        return Inertia::render('Grupo/Create', [
            'title' => $this->title,
            'amizades' => $this->usuariosAmizades(),
        ]);
    }

    public function store(GrupoValidator $request)
    {
        $this->grupos->criar($request->user(), $request->validated());

        return redirect()->route('grupos.index')->with('success', 'Grupo criado com sucesso!');
    }

    public function edit($id)
    {
        $grupo = Grupo::with('integrantes')->findOrFail($id);
        $this->authorize('update', $grupo);

        return Inertia::render('Grupo/Edit', [
            'title' => $this->title,
            'grupo' => $grupo,
            'amizades' => $this->usuariosAmizades(),
        ]);
    }

    public function update(GrupoValidator $request, $id)
    {
        $grupo = Grupo::findOrFail($id);
        $this->authorize('update', $grupo);

        $this->grupos->atualizar($grupo, $request->user(), $request->validated());

        return redirect()->route('grupos.index')->with('success', 'Grupo atualizado com sucesso!');
    }

    protected function usuariosAmizades()
    {
        return \App\Models\User::with('perfil')->whereIn('id', auth()->user()->amigoIds())->get();
    }

    protected function getAvailableFriends($grupoId)
    {
        $memberIds = Grupo::findOrFail($grupoId)->integrantes()->pluck('user_id');

        return \App\Models\User::with('perfil')
            ->whereIn('id', auth()->user()->amigoIds())
            ->whereNotIn('id', $memberIds)
            ->get();
    }

    public function destroy($id)
    {
        $grupo = Grupo::findOrFail($id);
        $this->authorize('delete', $grupo);

        $this->grupos->excluir($grupo);

        return redirect()->route('grupos.index')->with('success', 'Grupo excluído com sucesso!');
    }

    public function leave($id)
    {
        $this->grupos->sair(Grupo::findOrFail($id), auth()->user());

        return response()->json(['success' => true, 'message' => 'Você saiu do grupo com sucesso!']);
    }

    public function removeMember(Request $request, $id)
    {
        $grupo = Grupo::findOrFail($id);
        $this->authorize('manage', $grupo);

        $memberId = $request->validate(['member_id' => 'required|string'])['member_id'];
        $this->grupos->removerMembro($grupo, auth()->user(), $memberId);

        return response()->json(['success' => true, 'message' => 'Membro removido com sucesso!']);
    }

    public function makeAdmin(Request $request, $id)
    {
        $grupo = Grupo::findOrFail($id);
        $this->authorize('promote', $grupo);

        $memberId = $request->validate(['member_id' => 'required|string'])['member_id'];
        $this->grupos->promoverAdmin($grupo, $memberId);

        return response()->json(['success' => true, 'message' => 'Novo administrador atribuído!']);
    }

    public function inviteFriends(Request $request, $id)
    {
        $grupo = Grupo::findOrFail($id);
        $this->authorize('manage', $grupo);

        $friendIds = $request->validate([
            'friend_ids' => 'required|array|min:1',
            'friend_ids.*' => 'string|exists:users,id',
        ])['friend_ids'];

        $r = $this->grupos->convidarAmigos($grupo, auth()->user(), $friendIds);

        $message = $r['adicionados'].' membro(s) adicionado(s) ao grupo';
        if ($r['ja_membros'] > 0) {
            $message .= '. '.$r['ja_membros'].' já eram membros.';
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function generateInviteLink($id)
    {
        $grupo = Grupo::findOrFail($id);
        $this->authorize('manage', $grupo);

        $token = $this->grupos->gerarConvite($grupo);

        return response()->json(['success' => true, 'token' => $token, 'link' => route('grupos.join', $token)]);
    }

    public function joinByToken($token)
    {
        try {
            [$grupo, $resultado] = $this->grupos->entrarPorConvite($token, auth()->user());
        } catch (\App\Exceptions\RegraDeNegocioException $e) {
            return redirect()->route('grupos.index')->withErrors(['error' => $e->getMessage()]);
        }

        return match ($resultado) {
            'ja_membro' => redirect()->route('grupos.show', $grupo->id)->with('success', 'Você já é membro deste grupo'),
            'pendente' => redirect()->route('grupos.index')->with('success', 'Solicitação enviada! O administrador verá sua solicitação.'),
            'entrou' => redirect()->route('grupos.show', $grupo->id)->with('success', 'Você entrou no grupo com sucesso!'),
        };
    }
}
