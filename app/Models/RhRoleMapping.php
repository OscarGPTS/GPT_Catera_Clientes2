<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RhRoleMapping extends Model
{
    protected $table = 'rh_role_mapping';

    protected $fillable = [
        'puesto_rh', 'rol_sistema', 'prioridad', 'departamento_filter', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'prioridad' => 'integer',
            'activo' => 'boolean',
        ];
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    public function scopePorPrioridad($query)
    {
        return $query->orderBy('prioridad', 'desc');
    }
}
