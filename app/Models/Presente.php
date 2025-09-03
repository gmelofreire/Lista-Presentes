<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Presente extends Model
{
    use HasUuid;
    protected $table = "presentes";
    protected $primaryKey = "id";
    protected $keyType = "string";
    public $incrementing = false;
    protected $fillable = [
        "id",
        "nome",
        "descricao",
        "preco",
        "link",
        "image_url",
        "anotacoes",
        "cadastrado_por",
        "comprado",
        "avaliacao",
        "lista_id",
    ];

    public function cadastradoPor()
    {
        return $this->belongsTo(User::class, "cadastrado_por");
    }

    public function lista()
    {
        return $this->belongsTo(Lista::class, 'lista_id', 'id');
    }
}
