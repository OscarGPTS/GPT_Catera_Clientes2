<?php

namespace App\Models\Chat;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ChatCanal extends Model
{
    protected $table = 'chat_canales';

    protected $fillable = [
        'tipo', 'contexto_id', 'nombre', 'descripcion', 'creado_por_id',
    ];

    protected function casts(): array
    {
        return [
            'contexto_id' => 'integer',
        ];
    }

    public function miembros()
    {
        return $this->hasMany(ChatCanalMiembro::class, 'canal_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_canal_miembros', 'canal_id', 'user_id')
            ->withPivot('rol_en_canal', 'joined_at')
            ->withTimestamps();
    }

    public function mensajes()
    {
        return $this->hasMany(ChatMensaje::class, 'canal_id');
    }

    public function ultimoMensaje()
    {
        return $this->hasOne(ChatMensaje::class, 'canal_id')->latestOfMany();
    }

    public function lecturas()
    {
        return $this->hasMany(ChatLectura::class, 'canal_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'creado_por_id');
    }

    public function scopeProyecto($query)
    {
        return $query->where('tipo', 'proyecto');
    }

    public function scopeDepartamento($query)
    {
        return $query->where('tipo', 'departamento');
    }

    public function scopePrivado($query)
    {
        return $query->where('tipo', 'privado');
    }

    public function scopeDireccion($query)
    {
        return $query->where('tipo', 'direccion');
    }
}
