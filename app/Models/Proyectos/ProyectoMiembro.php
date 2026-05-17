<?php

namespace App\Models\Proyectos;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProyectoMiembro extends Model
{
    protected $table = 'proyecto_miembros';

    protected $fillable = [
        'proyecto_id',
        'user_id',
        'rol',
    ];

    // ── Roles disponibles ───────────────────────────────────────────────────
    const ROL_CREADOR              = 'creador';
    const ROL_DIRECTOR_DN          = 'director_dn';
    const ROL_GERENTE_PROYECTOS    = 'gerente_proyectos';
    const ROL_GERENTE_OPERACIONES  = 'gerente_operaciones';
    const ROL_INGENIERO_COSTOS     = 'ingeniero_costos';
    const ROL_INGENIERO_PROYECTOS  = 'ingeniero_proyectos';
    const ROL_TRAINEE              = 'trainee';

    public static array $rolesLabels = [
        'creador'              => 'Creador',
        'director_dn'          => 'Director DN',
        'gerente_proyectos'    => 'Gerente de Proyectos',
        'gerente_operaciones'  => 'Gerente de Operaciones',
        'ingeniero_costos'     => 'Ingeniero de Costos',
        'ingeniero_proyectos'  => 'Ingeniero de Proyectos',
        'trainee'              => 'Trainee',
    ];

    // ── Relaciones ──────────────────────────────────────────────────────────
    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────
    public function getRolLabelAttribute(): string
    {
        return static::$rolesLabels[$this->rol] ?? ($this->rol ?? 'Sin rol');
    }
}
