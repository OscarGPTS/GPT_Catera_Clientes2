<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionSnapshot extends Model
{
    use HasFactory;

    protected $table = 'asignaciones_personas';

    protected $fillable = [
        'user_id',
        'mes',
        'año',
        'cp_asignados',
        'cp_ejecutados',
        'cp_remanentes',
        'cp_residual_anterior',
        'dn_activos',
        'dn_stand_by',
        'dn_cerrados',
        'dn_cancelados',
        'total_servicio',
        'total_suministro',
        'gerencia_regional',
    ];

    protected function casts(): array
    {
        return [
            'mes' => 'integer',
            'año' => 'integer',
            'cp_asignados' => 'integer',
            'cp_ejecutados' => 'integer',
            'cp_remanentes' => 'integer',
            'cp_residual_anterior' => 'integer',
            'dn_activos' => 'integer',
            'dn_stand_by' => 'integer',
            'dn_cerrados' => 'integer',
            'dn_cancelados' => 'integer',
            'total_servicio' => 'decimal:2',
            'total_suministro' => 'decimal:2',
            'generado_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}