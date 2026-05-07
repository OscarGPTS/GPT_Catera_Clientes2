<?php

namespace App\Models;

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
        return $this->hasMany(\App\Models\Proyectos\Proyecto::class, 'director_dn_id');
    }

    public function proyectosComoGerente()
    {
        return $this->hasMany(\App\Models\Proyectos\Proyecto::class, 'gerente_proyectos_id');
    }

    public function proyectosComoIngeniero()
    {
        return $this->hasMany(\App\Models\Proyectos\Proyecto::class, 'ingeniero_proyectos_id');
    }
}
