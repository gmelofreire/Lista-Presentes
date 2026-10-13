<?php

namespace App\Events;

use App\Models\Notificacao;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Fato de domínio: já aconteceu. Os efeitos (notificar, enviar push/e-mail) são decididos pelos listeners. */
class NotificacaoCriada implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public Notificacao $notificacao) {}
}
