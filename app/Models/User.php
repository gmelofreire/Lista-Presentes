<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuid;

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

    public function perfil()
    {
        return $this->hasOne(Perfil::class);
    }

    public function listas()
{
    return $this->belongsToMany(
        Lista::class,
        'lista_usuarios',
        'Usuario_UUID',
        'Lista_UUID',
        'id',
        'id'
    );
}

}
