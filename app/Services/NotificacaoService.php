<?php

namespace App\Services;

use App\Events\NotificacaoCriada;
use App\Models\Notificacao;

class NotificacaoService
{
    public function notificar(string $userId, string $tipo, string $titulo, string $mensagem, ?string $link = null): Notificacao
    {
        $notificacao = Notificacao::create([
            'user_id' => $userId,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensagem' => $mensagem,
            'link' => $link,
        ]);

        NotificacaoCriada::dispatch($notificacao);

        return $notificacao;
    }
}
