<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Lista extends Model
{
    use HasUuid;

    protected $table = 'listas';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $fillable = [
        'nome',
        'descricao',
        'cadastrado_por_id',
        'status',
        'visibilidade',
        'data_evento',
        'grupo_id',
        'image_url',
        'token_convite',
        'token_expira_em',
    ];

    protected $hidden = ['token_convite'];

    protected function casts(): array
    {
        return ['token_expira_em' => 'datetime'];
    }

    public function cadastradoPor()
    {
        return $this->belongsTo(User::class, 'cadastrado_por_id');
    }

    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'lista_participantes', 'lista_id', 'usuario_id')
            ->withPivot('papel')
            ->withTimestamps();
    }

    public function presentes()
    {
        return $this->hasMany(Presente::class, 'lista_id', 'id');
    }

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function participantes()
    {
        return $this->hasMany(ListaParticipante::class, 'lista_id');
    }

    public function adicionarParticipante(string $usuarioId, string $papel = 'visualizador'): void
    {
        $this->participantes()->firstOrCreate(['usuario_id' => $usuarioId], ['papel' => $papel]);
    }

    public function pertenceA(User|string $user): bool
    {
        return $this->cadastrado_por_id === ($user instanceof User ? $user->id : $user);
    }
}
