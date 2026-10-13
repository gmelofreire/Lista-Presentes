<?php

namespace Tests\Feature\Seguranca;

use App\Models\Amizade;
use App\Models\Categoria;
use App\Models\Grupo;
use App\Models\Lista;
use App\Models\Presente;
use App\Models\User;

trait CriaDominio
{
    protected function lista(User $dono, array $extra = []): Lista
    {
        $lista = Lista::create(array_merge([
            'nome' => 'Lista',
            'cadastrado_por_id' => $dono->id,
            'visibilidade' => 'privada',
        ], $extra));
        $lista->adicionarParticipante($dono->id, 'admin');

        return $lista;
    }

    protected function presente(Lista $lista, User $autor): Presente
    {
        return Presente::create([
            'nome' => 'Presente',
            'descricao' => 'x',
            'lista_id' => $lista->id,
            'cadastrado_por' => $autor->id,
        ]);
    }

    protected function grupo(User $dono): Grupo
    {
        $grupo = Grupo::create(['nome' => 'Grupo', 'cadastrado_por' => $dono->id]);
        $grupo->integrantes()->attach($dono->id, ['role' => 'proprietario']);

        return $grupo;
    }

    protected function categoria(User $dono): Categoria
    {
        return Categoria::create(['nome' => 'Cat', 'hex_cor' => '#000000', 'descricao' => 'x', 'cadastrado_por' => $dono->id]);
    }

    protected function amigos(User $a, User $b): void
    {
        Amizade::create(['usuario_id' => $a->id, 'amigo_id' => $b->id, 'status' => 'aceito']);
    }
}
