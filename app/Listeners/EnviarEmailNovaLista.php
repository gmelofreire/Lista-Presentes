<?php

namespace App\Listeners;

use App\Events\NovaListaNoGrupo;
use App\Mail\NovaListaGrupoMailable;
use Illuminate\Support\Facades\Mail;

/**
 * Um e-mail enfileirado por destinatário: se um falhar e for refeito, os demais não são reenviados.
 */
class EnviarEmailNovaLista
{
    public function handle(NovaListaNoGrupo $e): void
    {
        $e->grupo->integrantes()->where('user_id', '!=', $e->criador->id)->get()->each(
            fn ($membro) => Mail::to($membro->email)->queue(new NovaListaGrupoMailable($e->lista, $e->grupo, $e->criador))
        );
    }
}
