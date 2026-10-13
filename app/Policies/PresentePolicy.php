<?php

namespace App\Policies;

use App\Models\Presente;
use App\Models\User;

class PresentePolicy
{
    public function view(User $user, Presente $presente): bool
    {
        return $user->can('view', $presente->lista);
    }

    public function update(User $user, Presente $presente): bool
    {
        return $user->can('update', $presente->lista);
    }

    public function delete(User $user, Presente $presente): bool
    {
        return $this->update($user, $presente);
    }

    /** Reservar: participante que vê a lista, exceto o dono dela (a surpresa é para ele). */
    public function reservar(User $user, Presente $presente): bool
    {
        $lista = $presente->lista;

        return ! $lista->pertenceA($user) && $user->can('view', $lista);
    }
}
