<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NovaListaNoGrupo
{
    use Dispatchable, SerializesModels;

    public $lista;

    public $grupo;

    public $criador;

    public function __construct($lista, $grupo, $criador)
    {
        $this->lista = $lista;
        $this->grupo = $grupo;
        $this->criador = $criador;
    }
}
