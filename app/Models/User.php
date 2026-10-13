<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, HasUuid, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'perfil_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->perfil()->create([
                'user_id' => $user->id,
            ]);
        });
    }

    public function dispositivos()
    {
        return $this->hasMany(Dispositivo::class);
    }

    public function perfil()
    {
        return $this->hasOne(Perfil::class);
    }

    public function listas()
    {
        return $this->belongsToMany(Lista::class, 'lista_participantes', 'usuario_id', 'lista_id')
            ->withPivot('papel');
    }

    public function presentes()
    {
        return $this->hasMany(Presente::class, 'cadastrado_por');
    }

    public function categorias()
    {
        return $this->hasMany(Categoria::class, 'cadastrado_por');
    }

    public function grupos()
    {
        return $this->belongsToMany(
            Grupo::class,
            'grupo_usuario',
            'user_id',
            'grupo_id',
            'id',
            'id'
        );
    }

    public function amizades()
    {
        return $this->hasMany(Amizade::class, 'usuario_id')
            ->orWhere('amigo_id', $this->id);
    }

    /** Ids dos amigos com amizade aceita, em qualquer sentido da relação. */
    public function amigoIds(): \Illuminate\Support\Collection
    {
        return Amizade::where('status', 'aceito')
            ->where(fn ($q) => $q->where('usuario_id', $this->id)->orWhere('amigo_id', $this->id))
            ->get(['usuario_id', 'amigo_id'])
            ->map(fn ($a) => $a->usuario_id === $this->id ? $a->amigo_id : $a->usuario_id)
            ->values();
    }

    public function amizadesAtivas()
    {
        return $this->hasMany(Amizade::class, 'usuario_id')
            ->where('status', 'aceito')
            ->orWhere(function ($query) {
                $query->where('amigo_id', $this->id)
                    ->where('status', 'aceito');
            });
    }

    public function amizadesPendentes()
    {
        return $this->hasMany(Amizade::class, 'amigo_id')
            ->where('status', 'pendente');
    }
}
