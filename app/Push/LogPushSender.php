<?php

namespace App\Push;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class LogPushSender implements PushSender
{
    public function enviar(Collection $dispositivos, string $titulo, string $mensagem, array $dados = []): array
    {
        foreach ($dispositivos as $d) {
            Log::info('push', ['dispositivo' => $d->id, 'plataforma' => $d->plataforma, 'titulo' => $titulo, 'mensagem' => $mensagem, 'dados' => $dados]);
        }

        return [];
    }
}
