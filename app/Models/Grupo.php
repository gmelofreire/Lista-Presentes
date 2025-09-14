<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasUuid;
    protected $fillable = [
        "nome",
        "descricao",
        "image_url",
        "cadastrado_por",
        "user_id",
    ];

    public function usuarios()
    {
        return $this->belongsToMany(
            User::class,
            'grupo_usuario',
            'grupo_id',
            'user_id',
            'id',
            'id'
        );
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function integrantes()
    {
        return $this->belongsToMany(
            User::class,
            'grupo_usuario',
            'grupo_id',
            'user_id',
            'id',
            'id'
        );
    }

    public function listas()
    {
        return $this->hasMany(Lista::class);
    }

}
