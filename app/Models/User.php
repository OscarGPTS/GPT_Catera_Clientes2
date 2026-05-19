<?php

namespace App\Models;

use App\Models\Chat\ChatCanal;
use App\Models\Chat\ChatCanalMiembro;
use App\Models\Chat\ChatLectura;
use App\Models\Chat\ChatMencion;
use App\Models\Chat\ChatMensaje;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'avatar_url',
        'departamento', 'puesto', 'employee_id',
        'es_socio', 'es_socio_override', 'status',
        'last_login_at',
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
            'es_socio' => 'boolean',
            'es_socio_override' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function authProviders()
    {
        return $this->hasMany(AuthProvider::class);
    }

    public function proyectosComoDirectorDn()
    {
        return $this->belongsToMany(\App\Models\Proyectos\Proyecto::class, 'proyecto_miembros', 'user_id', 'proyecto_id')
            ->wherePivot('rol', 'director_dn')
            ->withTimestamps();
    }

    public function proyectosComoGerente()
    {
        return $this->belongsToMany(\App\Models\Proyectos\Proyecto::class, 'proyecto_miembros', 'user_id', 'proyecto_id')
            ->wherePivot('rol', 'gerente_proyectos')
            ->withTimestamps();
    }

    public function proyectosComoIngeniero()
    {
        return $this->belongsToMany(\App\Models\Proyectos\Proyecto::class, 'proyecto_miembros', 'user_id', 'proyecto_id')
            ->wherePivot('rol', 'ingeniero_proyectos')
            ->withTimestamps();
    }

    public function chatCanales()
    {
        return $this->belongsToMany(ChatCanal::class, 'chat_canal_miembros', 'user_id', 'canal_id')
            ->withPivot('rol_en_canal', 'joined_at')
            ->withTimestamps();
    }

    public function chatMembresias()
    {
        return $this->hasMany(ChatCanalMiembro::class, 'user_id');
    }

    public function chatMensajes()
    {
        return $this->hasMany(ChatMensaje::class, 'user_id');
    }

    public function chatMenciones()
    {
        return $this->hasMany(ChatMencion::class, 'user_id');
    }

    public function chatMencionesNoLeidas()
    {
        return $this->hasMany(ChatMencion::class, 'user_id')->whereNull('leido_at');
    }

    public function chatLecturas()
    {
        return $this->hasMany(ChatLectura::class, 'user_id');
    }

    public function esAdmin(): bool
    {
        return $this->hasRole(['super_admin', 'direccion_general']);
    }
}
