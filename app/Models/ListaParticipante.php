<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListaParticipante extends Model
{
    protected $table = 'lista_participantes';

    protected $fillable = [
        'lista_id',
        'usuario_id',
        'papel',
    ];

    public function lista()
    {
        return $this->belongsTo(Lista::class, 'lista_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
