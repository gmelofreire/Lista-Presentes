<?php

namespace App\Policies;

use App\Models\Lista;
use App\Models\User;

class ListaPolicy
{
    private function papel(User $user, Lista $lista): ?string
    {
        return $lista->participantes()->where('usuario_id', $user->id)->value('papel');
    }

    public function view(User $user, Lista $lista): bool
    {
        return $this->papel($user, $lista) !== null || $lista->visibilidade === 'publica';
    }

    /** Editar a lista e seus presentes: admin ou editor. */
    public function update(User $user, Lista $lista): bool
    {
        return in_array($this->papel($user, $lista), ['admin', 'editor'], true);
    }

    public function delete(User $user, Lista $lista): bool
    {
        return $this->manage($user, $lista);
    }

    /** Gerenciar participantes e convites: somente admin. */
    public function manage(User $user, Lista $lista): bool
    {
        return $this->papel($user, $lista) === 'admin';
    }
}
