<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amizade extends Model
{
    protected $fillable = [
        "usuario_id",
        "amigo_id",
        "status",
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($amizade) {
            if ($amizade->usuario_id > $amizade->amigo_id) {
                [$amizade->usuario_id, $amizade->amigo_id] = [
                    $amizade->amigo_id,
                    $amizade->usuario_id,
                ];
            }
        });
    }

    public function scopeEntre($query, $user1, $user2)
    {
        return $query->where(function ($q) use ($user1, $user2) {
            $q->where('usuario_id', $user1)
                ->where('amigo_id', $user2);
        })->orWhere(function ($q) use ($user1, $user2) {
            $q->where('usuario_id', $user2)
                ->where('amigo_id', $user1);
        });
    }


}
