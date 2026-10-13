<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Notificacao */
class NotificacaoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tipo' => $this->tipo,
            'titulo' => $this->titulo,
            'mensagem' => $this->mensagem,
            'link' => $this->link,
            'lida' => (bool) $this->lido,
            'lida_em' => $this->data_lido,
            'created_at' => $this->created_at,
        ];
    }
}
