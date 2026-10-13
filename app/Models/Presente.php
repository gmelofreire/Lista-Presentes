<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Presente extends Model
{
    use HasUuid;

    protected $table = 'presentes';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'nome',
        'descricao',
        'preco',
        'link',
        'image_url',
        'anotacoes',
        'cadastrado_por',
        'comprado',
        'avaliacao',
        'lista_id',
    ];

    /** Nunca expõe quem reservou; o front recebe apenas `reservado` e `reservado_por_mim`. */
    protected $hidden = ['reservado_por', 'reservado_em'];

    protected $appends = ['reservado', 'reservado_por_mim'];

    protected function casts(): array
    {
        return [
            'comprado' => 'boolean',
            'preco' => 'decimal:2',
            'reservado_em' => 'datetime',
        ];
    }

    public function getReservadoAttribute(): bool
    {
        return $this->reservado_por !== null;
    }

    /** Verdadeiro só para quem reservou; ninguém mais (nem o dono) vê o reservante. */
    public function getReservadoPorMimAttribute(): bool
    {
        $userId = auth()->id();

        return $userId !== null && $this->reservado_por === $userId;
    }

    public function reservadoPor()
    {
        return $this->belongsTo(User::class, 'reservado_por');
    }

    public function cadastradoPor()
    {
        return $this->belongsTo(User::class, 'cadastrado_por');
    }

    public function lista()
    {
        return $this->belongsTo(Lista::class, 'lista_id', 'id');
    }

    public function categorias()
    {
        return $this->belongsToMany(
            Categoria::class,
            'categoria_presente',
            'presente_id',
            'categoria_id',
            'id',
            'id'
        );
    }
}
