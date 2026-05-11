<?php

namespace App\Models\Chat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ChatMensaje extends Model
{
    protected $table = 'chat_mensajes';

    protected $fillable = [
        'canal_id', 'user_id', 'parent_message_id', 'contenido', 'edited_at', 'attachments',
    ];

    protected function casts(): array
    {
        return [
            'edited_at' => 'datetime',
            'attachments' => 'array',
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

    public function parent()
    {
        return $this->belongsTo(ChatMensaje::class, 'parent_message_id');
    }

    public function replies()
    {
        return $this->hasMany(ChatMensaje::class, 'parent_message_id');
    }

    public function menciones()
    {
        return $this->hasMany(ChatMencion::class, 'mensaje_id');
    }

    public function scopeRaiz($query)
    {
        return $query->whereNull('parent_message_id');
    }

    public function scopeConReplies($query)
    {
        return $query->with('replies.user', 'replies.menciones.user');
    }
}
