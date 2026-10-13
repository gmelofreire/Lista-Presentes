<?php

namespace App\Events;

use App\Models\Grupo;
use App\Models\User;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** `$por` é quem convidou; nulo quando a pessoa entrou sozinha pelo link de convite. */
class MembroAdicionadoAoGrupo implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public Grupo $grupo, public User $membro, public ?User $por = null) {}
}
