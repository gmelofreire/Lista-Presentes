<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\UrlAbsoluta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Lista */
class ListaResource extends JsonResource
{
    use UrlAbsoluta;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'status' => $this->status,
            'visibilidade' => $this->visibilidade,
            'data_evento' => $this->data_evento,
            'imagem' => self::absoluta($this->image_url),
            'grupo_id' => $this->grupo_id,
            'dono_id' => $this->cadastrado_por_id,
            'meu_papel' => $this->whenLoaded('participantes', fn () => $this->participantes->firstWhere('usuario_id', $request->user()?->id)?->papel),
            'presentes_count' => $this->whenCounted('presentes'),
            'grupo' => GrupoResource::make($this->whenLoaded('grupo')),
            'presentes' => PresenteResource::collection($this->whenLoaded('presentes')),
            'participantes' => $this->whenLoaded('participantes', fn () => $this->participantes->map(fn ($p) => [
                'id' => $p->id,
                'papel' => $p->papel,
                'usuario' => UserResource::make($p->usuario),
            ])),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
