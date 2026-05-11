<?php

namespace App\Models\Chat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ChatCanalMiembro extends Model
{
    protected $table = 'chat_canal_miembros';

    protected $fillable = [
        'canal_id', 'user_id', 'rol_en_canal', 'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
        ];
    }

    public function canal()
    {
        return $this->belongsTo(ChatCanal::class, 'canal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
