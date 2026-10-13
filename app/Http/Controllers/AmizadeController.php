<?php

namespace App\Http\Controllers;

use App\Models\Amizade;
use App\Models\Grupo;
use App\Models\User;
use App\Services\AmizadeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AmizadeController extends Controller
{
    protected $title = 'Amizades';

    public function __construct(private AmizadeService $amizades) {}

    public function index()
    {
        $amizades = $this->separarAmizades();
        $amizadesPendentes = auth()->user()->amizadesPendentes()->with('user.perfil')->get();

        return Inertia::render('Amizade/Index', [
            'title' => $this->title,
            'amizades' => $amizades,
            'amizadesPendentes' => $amizadesPendentes,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->amizades->aceitar($request->user(), $id);

        return redirect()->back()->with('success', 'Amizade aceita com sucesso!');
    }

    public function show($id)
    {
        $amigo = User::with('perfil', 'grupos', 'listas')->findOrFail($id);
        $amizade = Amizade::entre($id, auth()->user()->id)->first();

        $grupos_comum = $this->gruposEmComum($amigo);

        return Inertia::render('Amizade/Show', [
            'title' => $this->title,
            'amigo' => $amigo,
            'amizade' => $amizade,
            'grupos' => $grupos_comum,
        ]);
    }

    public function store($id)
    {
        $this->amizades->solicitar(auth()->user(), User::findOrFail($id));

        return redirect()->route('amizades.show', $id)->with('status', 'Solicitação de amizade enviada.');
    }

    public function destroy($id)
    {
        $this->amizades->desfazer(auth()->user(), $id);

        return redirect()->route('amizades.show', $id)->with('status', 'Amizade desfeita.');
    }

    public function reject($id)
    {
        $this->amizades->recusar(auth()->user(), $id);

        return response()->json(['success' => true, 'message' => 'Solicitação rejeitada']);
    }

    public function cancel($id)
    {
        $this->amizades->cancelar(auth()->user(), $id);

        return response()->json(['success' => true, 'message' => 'Solicitação cancelada']);
    }

    public function block($id)
    {
        $this->amizades->bloquear(auth()->user(), $id);

        return response()->json(['success' => true, 'message' => 'Usuário bloqueado']);
    }

    public function unblock($id)
    {
        $this->amizades->desbloquear(auth()->user(), $id);

        return response()->json(['success' => true, 'message' => 'Usuário desbloqueado']);
    }

    public function separarAmizades()
    {
        $amizades = auth()->user()->amizadesAtivas()->with('user.perfil', 'user', 'amigo.perfil', 'amigo')->get();
        $user_id = auth()->user()->id;
        $amigos = [];
        foreach ($amizades as $key => $amizade) {
            if ($amizade->user->id == $user_id) {
                $amigos[$key] = $amizade->amigo;
                $amigos[$key]->grupos = $this->gruposEmComum($amizade->amigo);
            }
            if ($amizade->amigo->id == $user_id) {
                $amigos[$key] = $amizade->user;
                $amigos[$key]->grupos = $this->gruposEmComum($amizade->user);
            }

        }

        return $amigos;
    }

    public function gruposEmComum($amigo)
    {
        $user_id = auth()->user()->id;

        return Grupo::whereHas('integrantes', function ($q) use ($amigo) {
            $q->where('user_id', $amigo->id);
        })
            ->whereHas('integrantes', function ($q) use ($user_id) {
                $q->where('user_id', $user_id);
            })
            ->get();
    }

    public function buscarUsuarios(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $usuarios = User::with('perfil')
            ->where('id', '!=', auth()->id())
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('username', 'LIKE', "%{$query}%");
            })
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'avatar' => $user->perfil->image_url ?? null,
                ];
            });

        return response()->json($usuarios);
    }
}
