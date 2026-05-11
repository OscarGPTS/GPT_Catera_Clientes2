<?php

namespace App\Models\Chat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ChatLectura extends Model
{
    protected $table = 'chat_lecturas';

    protected $fillable = [
        'canal_id', 'user_id', 'ultimo_mensaje_leido_id',
    ];

    public function canal()
    {
        return $this->belongsTo(ChatCanal::class, 'canal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ultimoMensajeLeido()
    {
        return $this->belongsTo(ChatMensaje::class, 'ultimo_mensaje_leido_id');
    }
}
