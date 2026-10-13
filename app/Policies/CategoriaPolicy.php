<?php

namespace App\Policies;

use App\Models\Categoria;
use App\Models\User;

class CategoriaPolicy
{
    public function update(User $user, Categoria $categoria): bool
    {
        return $categoria->cadastrado_por === $user->id;
    }

    public function delete(User $user, Categoria $categoria): bool
    {
        return $this->update($user, $categoria);
    }
}
