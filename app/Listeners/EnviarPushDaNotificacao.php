<?php

namespace App\Listeners;

use App\Events\NotificacaoCriada;
use App\Push\PushSender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class EnviarPushDaNotificacao implements ShouldQueue
{
    public int $tries = 3;

    public function __construct(private PushSender $push) {}

    /** @return list<int> */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function handle(NotificacaoCriada $evento): void
    {
        $n = $evento->notificacao->fresh();

        // Idempotente: retry ou evento duplicado não reenvia.
        if (! $n || $n->push_enviado_em) {
            return;
        }

        $dispositivos = $n->user->dispositivos;
        if ($dispositivos->isNotEmpty()) {
            $invalidos = $this->push->enviar($dispositivos, $n->titulo, $n->mensagem, [
                'tipo' => $n->tipo, 'notificacao_id' => (string) $n->id, 'link' => (string) $n->link,
            ]);

            if ($invalidos) {
                $n->user->dispositivos()->whereIn('token', $invalidos)->delete();
            }
        }

        $n->update(['push_enviado_em' => now()]);
    }

    public function failed(NotificacaoCriada $evento, Throwable $e): void
    {
        Log::error('Falha ao enviar push', ['notificacao' => $evento->notificacao->id, 'erro' => $e->getMessage()]);
    }
}
