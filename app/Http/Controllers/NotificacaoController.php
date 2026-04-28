<?php

namespace App\Http\Controllers;

use App\Models\Notificacao;
use Inertia\Inertia;

class NotificacaoController extends Controller
{
    public function index()
    {
        $notificacoes = Notificacao::where('user_id', auth()->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $naoLidas = Notificacao::where('user_id', auth()->user()->id)
            ->where('lido', false)
            ->count();

        return Inertia::render('Notificacao/Index', [
            'notificacoes' => $notificacoes,
            'naoLidas' => $naoLidas,
        ]);
    }

    public function getNaoLidas()
    {
        $count = Notificacao::where('user_id', auth()->user()->id)
            ->where('lido', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function getRecentes()
    {
        $notificacoes = Notificacao::where('user_id', auth()->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json(['notificacoes' => $notificacoes]);
    }

    public function marcarLido($id)
    {
        $notificacao = Notificacao::where('id', $id)
            ->where('user_id', auth()->user()->id)
            ->first();

        if ($notificacao) {
            $notificacao->update([
                'lido' => true,
                'data_lido' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function marcarTodasLido()
    {
        Notificacao::where('user_id', auth()->user()->id)
            ->where('lido', false)
            ->update([
                'lido' => true,
                'data_lido' => now(),
            ]);

        return response()->json(['success' => true]);
    }
}
