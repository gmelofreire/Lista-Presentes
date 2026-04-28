<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

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

        $totalAmizades = $user->amizadesAtivas()->count();

        return Inertia::render('Dashboard', [
            'title' => 'Página Inicial',
            'amizadesPendentes' => $amizadesPendentes,
            'listasAtivas' => $listasAtivas,
            'gruposRecentes' => $gruposRecentes,
            'totalAmizades' => $totalAmizades,
        ]);
    }
}
