<?php

namespace App\Http\Controllers;

use App\Http\Requests\GrupoValidator;
use App\Models\Grupo;
use App\Services\FileUploadService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Inertia\Inertia;

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

        return Inertia::render('Grupo/Index', [
            'title' => $this->title,
            'grupos' => $grupos,
            'filters' => ['search' => $request->get('search', '')],
        ]);
    }

    public function show($id)
    {
        $grupo = Grupo::with(['integrantes' => function ($q) {
            $q->select('users.id', 'name', 'email', 'username')->with('perfil');
        }, 'listas.cadastradoPor.perfil'])->find($id);

        $grupo->integrantes->each(function ($membro) use ($grupo) {
            $membership = $grupo->integrantes()->where('user_id', $membro->id)->first();
            $membro->pivot = ['role' => $membership ? $membership->pivot->role : 'visualizador'];
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

    public function store(GrupoValidator $request, FileUploadService $uploader)
    {
        try {
            $dados = $request->validated();
            if (isset($dados['image_url']) && $dados['image_url'] instanceof UploadedFile) {
                $dados['image_url'] = $uploader->upload($dados['image_url'], 'grupos', $uploader->extensoesImagem);
            } else {
                $dados['image_url'] = '/img/default_cover.png';
            }

            if (isset($dados['banner_url']) && $dados['banner_url'] instanceof UploadedFile) {
                $dados['banner_url'] = $uploader->upload($dados['banner_url'], 'grupos', $uploader->extensoesImagem);
            }

            $grupo = Grupo::create($dados);

            $ownerId = auth()->user()->id;
            $grupo->integrantes()->attach($ownerId, ['role' => 'proprietario']);

            if (! empty($dados['integrante_ids'])) {
                foreach ($dados['integrante_ids'] as $memberId) {
                    if ($memberId !== $ownerId) {
                        $grupo->integrantes()->attach($memberId, ['role' => 'visualizador']);
                    }
                }
            }

            return redirect()->route('grupos.index')->with('success', 'Grupo criado com sucesso!');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $grupo = Grupo::with('integrantes')->find($id);

        return Inertia::render('Grupo/Edit', [
            'title' => $this->title,
            'grupo' => $grupo,
            'amizades' => $this->usuariosAmizades(),
        ]);
    }

    public function update(GrupoValidator $request, $id, FileUploadService $uploader)
    {
        try {
            $grupo = Grupo::find($id);
            $dados = $request->validated();

            if (isset($dados['image_url']) && $dados['image_url'] instanceof UploadedFile) {
                $dados['image_url'] = $uploader->upload($dados['image_url'], 'grupos', $uploader->extensoesImagem);
            } elseif (empty($dados['image_url'])) {
                $dados['image_url'] = '/img/default_cover.png';
            }

            if (isset($dados['banner_url']) && $dados['banner_url'] instanceof UploadedFile) {
                $dados['banner_url'] = $uploader->upload($dados['banner_url'], 'grupos', $uploader->extensoesImagem);
            }

            $grupo->update($dados);
            $grupo->integrantes()->detach();

            if (! empty($dados['integrante_ids'])) {
                foreach ($dados['integrante_ids'] as $memberId) {
                    $role = $memberId === $grupo->cadastrado_por ? 'proprietario' : 'visualizador';
                    $grupo->integrantes()->attach($memberId, ['role' => $role]);
                }
            }

            return redirect()->route('grupos.index')->with('success', 'Grupo atualizado com sucesso!');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    protected function usuariosAmizades()
    {
        $userId = auth()->id();
        $amizadesAceitas = auth()->user()->amizades()->where('status', 'aceito')->get();
        $amigoIds = $amizadesAceitas->map(function ($amizade) use ($userId) {
            return $amizade->usuario_id == $userId ? $amizade->amigo_id : $amizade->usuario_id;
        });

        return \App\Models\User::with('perfil')->whereIn('id', $amigoIds)->get();
    }

    protected function getAvailableFriends($grupoId)
    {
        $userId = auth()->id();

        $amizadesAceitas = auth()->user()->amizades()->where('status', 'aceito')->get();
        $amigoIds = $amizadesAceitas->map(function ($amizade) use ($userId) {
            return $amizade->usuario_id == $userId ? $amizade->amigo_id : $amizade->usuario_id;
        });

        $grupo = Grupo::find($grupoId);
        $memberIds = $grupo->integrantes()->pluck('user_id')->toArray();

        return \App\Models\User::with('perfil')
            ->whereIn('id', $amigoIds)
            ->whereNotIn('id', $memberIds)
            ->get();
    }

    public function destroy($id)
    {
        try {
            $grupo = Grupo::where('cadastrado_por', auth()->user()->id)->findOrFail($id);
            $grupo->integrantes()->detach();
            $grupo->listas()->update(['grupo_id' => null]);
            $grupo->delete();

            return redirect()->route('grupos.index')->with('success', 'Grupo excluído com sucesso!');
        } catch (Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function leave($id)
    {
        try {
            $userId = auth()->user()->id;
            $grupo = Grupo::findOrFail($id);

            if (! $grupo->integrantes()->where('user_id', $userId)->exists()) {
                return response()->json(['success' => false, 'message' => 'Você não é membro deste grupo'], 400);
            }

            if ($grupo->cadastrado_por == $userId) {
                return response()->json(['success' => false, 'message' => 'O proprietário não pode sair do grupo. Exclua o grupo ou transfira a propriedade.'], 400);
            }

            $grupo->integrantes()->detach($userId);

            return response()->json(['success' => true, 'message' => 'Você saiu do grupo com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function removeMember(Request $request, $id)
    {
        try {
            $grupo = Grupo::findOrFail($id);
            $memberId = $request->input('member_id');

            $userId = auth()->user()->id;

            // Owner can always remove members
            if ($grupo->cadastrado_por != $userId) {
                $membership = $grupo->integrantes()->where('user_id', $userId)->first();
                if (! $membership || ($membership->pivot->role ?? 'visualizador') !== 'admin') {
                    return response()->json(['success' => false, 'message' => 'Você não tem permissão para remover membros'], 403);
                }
            }

            if ($grupo->cadastrado_por == $memberId) {
                return response()->json(['success' => false, 'message' => 'Não é possível remover o proprietário'], 400);
            }

            if ($memberId == $userId) {
                return response()->json(['success' => false, 'message' => 'Use a opção Sair do Grupo para si mesmo'], 400);
            }

            $grupo->integrantes()->detach($memberId);

            return response()->json(['success' => true, 'message' => 'Membro removido com sucesso!']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function makeAdmin(Request $request, $id)
    {
        try {
            $grupo = Grupo::findOrFail($id);
            $memberId = $request->input('member_id');

            $userId = auth()->user()->id;
            if ($grupo->cadastrado_por != $userId) {
                return response()->json(['success' => false, 'message' => 'Apenas o proprietário pode tornar outro usuário administrador'], 403);
            }

            if (! $grupo->integrantes()->where('user_id', $memberId)->exists()) {
                return response()->json(['success' => false, 'message' => 'Membro não encontrado'], 404);
            }

            $grupo->integrantes()->updateExistingPivot($memberId, ['role' => 'admin']);

            return response()->json(['success' => true, 'message' => 'Novo administrador atribuído!']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function inviteFriends(Request $request, $id)
    {
        try {
            $grupo = Grupo::findOrFail($id);
            $userId = auth()->user()->id;
            $friendIds = $request->input('friend_ids', []);

            // Check permission (owner or admin can invite)
            if ($grupo->cadastrado_por != $userId) {
                $membership = $grupo->integrantes()->where('user_id', $userId)->first();
                if (! $membership || ($membership->pivot->role ?? 'visualizador') !== 'admin') {
                    return response()->json(['success' => false, 'message' => 'Você não tem permissão para convidar membros'], 403);
                }
            }

            if (empty($friendIds)) {
                return response()->json(['success' => false, 'message' => 'Selecione pelo menos um amigo para convidar'], 400);
            }

            $adicionados = [];
            $jaMembros = [];

            foreach ($friendIds as $friendId) {
                // Skip if already a member
                if ($grupo->integrantes()->where('user_id', $friendId)->exists()) {
                    $jaMembros[] = $friendId;

                    continue;
                }

                $grupo->integrantes()->attach($friendId, ['role' => 'visualizador']);
                $adicionados[] = $friendId;
            }

            $message = count($adicionados).' membro(s) adicionado(s) ao grupo';
            if (count($jaMembros) > 0) {
                $message .= '. '.count($jaMembros).' já eram membros.';
            }

            return response()->json(['success' => true, 'message' => $message]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function generateInviteLink($id)
    {
        try {
            $grupo = Grupo::findOrFail($id);
            $userId = auth()->user()->id;

            // Owner can always generate invite
            if ($grupo->cadastrado_por != $userId) {
                $membership = $grupo->integrantes()->where('user_id', $userId)->first();
                if (! $membership || ($membership->pivot->role ?? 'visualizador') !== 'admin') {
                    return response()->json(['success' => false, 'message' => 'Você não tem permissão para gerar convite'], 403);
                }
            }

            if (! $grupo->token_convite || ($grupo->token_expira_em && $grupo->token_expira_em->isPast())) {
                $grupo->update([
                    'token_convite' => \Illuminate\Support\Str::random(64),
                    'token_expira_em' => now()->addDays(30),
                ]);
                $grupo->refresh();
            }

            return response()->json([
                'success' => true,
                'token' => $grupo->token_convite,
                'link' => url('/grupos/join/'.$grupo->token_convite),
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function joinByToken($token)
    {
        try {
            $grupo = Grupo::where('token_convite', $token)->firstOrFail();

            if ($grupo->token_expira_em && $grupo->token_expira_em->isPast()) {
                return redirect()->route('grupos.index')->withErrors(['error' => 'Link de convite expirado']);
            }

            if (auth()->user()->grupos()->where('grupo_id', $grupo->id)->exists()) {
                return redirect()->route('grupos.show', $grupo->id)->with('success', 'Você já é membro deste grupo');
            }

            if ($grupo->visibilidade === 'privada') {
                return redirect()->route('grupos.index')->with('success', 'Solicitação enviada! O administrador verá sua solicitação.');
            }

            $grupo->integrantes()->attach(auth()->user()->id, ['role' => 'visualizador']);

            return redirect()->route('grupos.show', $grupo->id)->with('success', 'Você entrou no grupo com sucesso!');
        } catch (Exception $e) {
            return redirect()->route('grupos.index')->withErrors(['error' => 'Link de convite inválido']);
        }
    }
}
