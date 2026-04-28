<?php

namespace App\Listeners;

use App\Events\NovaListaNoGrupo;
use App\Mail\NovaListaGrupoMailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificarMembrosDoGrupo
{
    public function handle(NovaListaNoGrupo $event): void
    {
        $grupo = $event->grupo;
        $lista = $event->lista;
        $criador = $event->criador;

        $membros = $grupo->integrantes()
            ->where('user_id', '!=', $criador->id)
            ->get();

        foreach ($membros as $membro) {
            try {
                Mail::to($membro->email)->send(new NovaListaGrupoMailable($lista, $grupo, $criador));
            } catch (\Exception $e) {
                Log::error('Failed to send email: '.$e->getMessage());
            }
        }
    }
}
