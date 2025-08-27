<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    use HasUuid;
    protected $table = "perfis";
    protected $primaryKey = "id";

    protected $fillable = [
        "data_nascimento",
        "genero",
        "telefone",
        "image_url",
        "user_id",
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
