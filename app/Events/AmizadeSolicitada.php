<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Fato de domínio: já aconteceu. Os efeitos (notificar, enviar push/e-mail) são decididos pelos listeners. */
class AmizadeSolicitada implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(public User $de, public User $para) {}
}
