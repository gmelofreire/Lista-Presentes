<?php

namespace App\Events;

use App\Models\Grupo;
use App\Models\Lista;
use App\Models\User;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NovaListaNoGrupo implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public Lista $lista, public Grupo $grupo, public User $criador) {}
}
