<?php

namespace App\Http\Resources;

use App\Http\Resources\Concerns\UrlAbsoluta;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class UserResource extends JsonResource
{
    use UrlAbsoluta;

    public function toArray(Request $request): array
    {
        $eu = $request->user()?->id === $this->id;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'avatar' => $this->whenLoaded('perfil', fn () => self::absoluta($this->perfil?->image_url)),
            // Dados de contato só para o próprio usuário.
            'email' => $this->when($eu, $this->email),
            'perfil' => $this->when($eu && $this->relationLoaded('perfil'), fn () => [
                'telefone' => $this->perfil?->telefone,
                'data_nascimento' => $this->perfil?->data_nascimento,
                'genero' => $this->perfil?->genero,
                'biografia' => $this->perfil?->biografia,
            ]),
            // Papel no grupo ou na lista quando carregado via pivot.
            'papel' => $this->whenPivotLoaded('grupo_usuario', fn () => $this->pivot->role,
                fn () => $this->whenPivotLoaded('lista_participantes', fn () => $this->pivot->papel)),
        ];
    }
}
