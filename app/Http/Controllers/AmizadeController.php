<?php

namespace App\Http\Controllers;

use App\Models\Amizade;
use App\Models\Grupo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;

class AmizadeController extends Controller
{
    protected $title = 'Amizades';

    public function index()
    {
        $amizades = $this->separarAmizades();
        $amizadesPendentes = auth()->user()->amizadesPendentes()->with('user.perfil')->get();

        return Inertia::render('Amizade/Index', [
            'title' => $this->title,
            'amizades' => $amizades,
            'amizadesPendentes' => $amizadesPendentes
        ]);
    }

    public function update(Request $request, $id)
    {
        Amizade::where('usuario_id', $id)
            ->where('amigo_id', auth()->user()->id)
            ->update(['status' => 'aceito']);
        
        return redirect()->back()->with('success', 'Amizade aceita com sucesso!');
    }

    public function show($id)
    {
        $amigo = User::with('perfil', 'grupos', 'listas')->find($id);
        $amizade = Amizade::entre($id, auth()->user()->id)->first();

        $grupos_comum = $this->gruposEmComum($amigo);

        return Inertia::render('Amizade/Show', [
            'title' => $this->title,
            'amigo' => $amigo, 
            'amizade' => $amizade,
            'grupos' => $grupos_comum
        ]);
    }

    public function store($id)
    {
        $amizade = Amizade::create([
            'usuario_id' => auth()->user()->id,
            'amigo_id' => $id,
            'status' => 'pendente',
        ]);
        return redirect()->route('amizades.show', $id);
    }

    public function destroy($id)
{
    $amizade = Amizade::entre($id, auth()->user()->id)->first();

    if (!$amizade) {
        return redirect()->route('amizades.index')->with('error', 'Amizade não encontrada');
    }

    Amizade::entre($id, auth()->user()->id)->delete();

    return redirect()->route('amizades.show', $id);
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
                    'avatar' => $user->perfil->avatar ?? null
                ];
            });

        return response()->json($usuarios);
    }
}
