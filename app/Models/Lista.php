<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Lista extends Model
{
    use HasUuid;
    protected $table = "listas";
    protected $primaryKey = "id";
    protected $keyType = "string";
    protected $fillable = [
        'nome',
        'descricao',
        'cadastrado_por_id',
        'status',
        'visibilidade',
        'data_evento',
        'image_url',
    ];

    public function cadastradoPor()
    {
        return $this->belongsTo(User::class, 'cadastrado_por_id');
    }

    public function usuarios()
{
    return $this->belongsToMany(
        User::class,
        'lista_usuarios',
        'Lista_UUID',    // foreign key da model atual
        'Usuario_UUID',  // foreign key da outra model
        'id',
        'id'
    );
}

}
