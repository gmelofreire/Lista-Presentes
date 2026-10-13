<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notificacao extends Model
{
    protected $table = 'notificacoes';

    protected $fillable = [
        'user_id',
        'tipo',
        'titulo',
        'mensagem',
        'link',
        'lido',
        'data_lido',
        'push_enviado_em',
    ];

    protected $casts = [
        'lido' => 'boolean',
        'data_lido' => 'datetime',
        'push_enviado_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
