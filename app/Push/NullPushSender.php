<?php

namespace App\Push;

use Illuminate\Support\Collection;

class NullPushSender implements PushSender
{
    public function enviar(Collection $dispositivos, string $titulo, string $mensagem, array $dados = []): array
    {
        return [];
    }
}
