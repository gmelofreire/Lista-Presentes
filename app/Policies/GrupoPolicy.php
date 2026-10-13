<?php

namespace App\Policies;

use App\Models\Grupo;
use App\Models\User;

class GrupoPolicy
{
    private function papel(User $user, Grupo $grupo): ?string
    {
        if ($grupo->cadastrado_por === $user->id) {
            return 'proprietario';
        }

        return $grupo->integrantes()->where('user_id', $user->id)->first()?->pivot->role;
    }

    public function view(User $user, Grupo $grupo): bool
    {
        return $this->papel($user, $grupo) !== null;
    }

    /** Convidar, remover membros, gerar convite, editar: proprietário ou admin. */
    public function manage(User $user, Grupo $grupo): bool
    {
        return in_array($this->papel($user, $grupo), ['proprietario', 'admin'], true);
    }

    public function update(User $user, Grupo $grupo): bool
    {
        return $this->manage($user, $grupo);
    }

    public function delete(User $user, Grupo $grupo): bool
    {
        return $grupo->cadastrado_por === $user->id;
    }

    public function promote(User $user, Grupo $grupo): bool
    {
        return $grupo->cadastrado_por === $user->id;
    }
}
