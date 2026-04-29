<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasUuid;

    protected $table = 'categorias';

    protected $primaryKey = 'id';

    protected $fillable = [
        'nome',
        'hex_cor',
        'descricao',
        'cadastrado_por',
        'icone',
        'parent_id',
        'ordem',
        'e_default',
    ];

    protected $casts = [
        'e_default' => 'boolean',
    ];

    public function cadastradoPor()
    {
        return $this->belongsTo(User::class, 'cadastrado_por');
    }

    public function presentes()
    {
        return $this->belongsToMany(
            Presente::class,
            'categoria_presente',
            'categoria_id',
            'presente_id',
            'id',
            'id'
        );
    }

    public function parent()
    {
        return $this->belongsTo(Categoria::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Categoria::class, 'parent_id')->orderBy('ordem');
    }

    public function getPresentesCountAttribute()
    {
        return $this->presentes()->count();
    }
}
