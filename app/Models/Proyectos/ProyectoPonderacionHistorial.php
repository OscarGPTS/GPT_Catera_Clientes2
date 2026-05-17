<?php

namespace App\Models\Proyectos;

use App\Models\Ponderacion;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProyectoPonderacionHistorial extends Model
{
    protected $table = 'proyecto_ponderacion_historial';

    protected $fillable = [
        'proyecto_id',
        'ponderacion_id',
        'anio',
        'mes',
        'user_id',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'mes'  => 'integer',
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function ponderacion(): BelongsTo
    {
        return $this->belongsTo(Ponderacion::class, 'ponderacion_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Etiqueta corta del mes (Ene, Feb…) */
    public function getMesLabelAttribute(): string
    {
        $labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        return ($labels[$this->mes - 1] ?? '?') . ' ' . substr((string) $this->anio, -2);
    }
}
