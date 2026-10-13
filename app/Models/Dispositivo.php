<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Dispositivo extends Model
{
    use HasUuid;

    protected $table = 'dispositivos';

    protected $fillable = ['user_id', 'token', 'plataforma', 'nome', 'ultimo_uso_em'];

    protected $hidden = ['token'];

    protected function casts(): array
    {
        return ['ultimo_uso_em' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
