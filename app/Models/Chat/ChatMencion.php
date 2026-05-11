<?php

namespace App\Models\Chat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ChatMencion extends Model
{
    protected $table = 'chat_menciones';

    protected $fillable = [
        'mensaje_id', 'user_id', 'leido_at',
    ];

    protected function casts(): array
    {
        return [
            'leido_at' => 'datetime',
        ];
    }

    public function mensaje()
    {
        return $this->belongsTo(ChatMensaje::class, 'mensaje_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeNoLeidas($query)
    {
        return $query->whereNull('leido_at');
    }
}
