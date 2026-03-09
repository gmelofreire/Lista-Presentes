<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Carregar relacionamentos necessários
        $amizadesPendentes = $user->amizadesPendentes()->with('user.perfil')->get();
        
        $listasAtivas = \App\Models\Lista::where('cadastrado_por_id', $user->id)
            ->with(['grupo'])
            ->latest()
            ->take(5)
            ->get();
            
        $gruposRecentes = $user->grupos()
            ->withPivot('created_at')
            ->orderBy('grupo_usuario.created_at', 'desc')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'title' => 'Página Inicial',
            'amizadesPendentes' => $amizadesPendentes,
            'listasAtivas' => $listasAtivas,
            'gruposRecentes' => $gruposRecentes,
        ]);
    }
}
