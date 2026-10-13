<?php

namespace App\Push;

use App\Models\Dispositivo;
use Illuminate\Support\Collection;

interface PushSender
{
    /**
     * @param  Collection<int, Dispositivo>  $dispositivos
     * @param  array<string, string>  $dados  payload extra para o app (tipo, link, id)
     * @return list<string> tokens inválidos que devem ser removidos
     */
    public function enviar(Collection $dispositivos, string $titulo, string $mensagem, array $dados = []): array;
}
