<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Categoria */
class CategoriaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'hex_cor' => $this->hex_cor,
            'icone' => $this->icone,
            'parent_id' => $this->parent_id,
            'ordem' => $this->ordem,
            'padrao' => (bool) $this->e_default,
        ];
    }
}
