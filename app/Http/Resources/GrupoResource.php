<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\UrlAbsoluta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Grupo */
class GrupoResource extends JsonResource
{
    use UrlAbsoluta;

    public function toArray(Request $request): array
    {
        $meuId = $request->user()?->id;

        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'imagem' => self::absoluta($this->image_url),
            'banner' => self::absoluta($this->banner_url),
            'visibilidade' => $this->visibilidade,
            'dono_id' => $this->cadastrado_por,
            'meu_papel' => $this->whenLoaded('integrantes', fn () => $this->integrantes->firstWhere('id', $meuId)?->pivot->role),
            'integrantes_count' => $this->whenCounted('integrantes'),
            'integrantes' => UserResource::collection($this->whenLoaded('integrantes')),
            'listas' => ListaResource::collection($this->whenLoaded('listas')),
            'created_at' => $this->created_at,
        ];
    }
}
