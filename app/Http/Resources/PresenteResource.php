<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\UrlAbsoluta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Presente */
class PresenteResource extends JsonResource
{
    use UrlAbsoluta;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'lista_id' => $this->lista_id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'preco' => $this->preco !== null ? (float) $this->preco : null,
            'link' => $this->link,
            'imagem' => self::absoluta($this->image_url),
            'anotacoes' => $this->anotacoes,
            'avaliacao' => $this->avaliacao,
            // Nunca expõe quem reservou: o dono da lista só vê "reservado".
            'reservado' => $this->reservado,
            'reservado_por_mim' => $this->reservado_por_mim,
            'categorias' => CategoriaResource::collection($this->whenLoaded('categorias')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
