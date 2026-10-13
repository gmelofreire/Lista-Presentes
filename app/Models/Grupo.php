<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    use HasUuid;

    protected $fillable = [
        'nome',
        'descricao',
        'image_url',
        'cadastrado_por',
        'user_id',
        'banner_url',
        'visibilidade',
        'token_convite',
        'token_expira_em',
    ];

    protected $hidden = ['token_convite'];

    protected function casts(): array
    {
        return ['token_expira_em' => 'datetime'];
    }

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
        )->withPivot('role');
    }

    public function listas()
    {
        return $this->hasMany(Lista::class);
    }
}
