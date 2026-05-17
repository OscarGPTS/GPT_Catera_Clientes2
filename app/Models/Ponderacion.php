<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Proyectos\ProyectoPonderacionHistorial;

class Ponderacion extends Model
{
    protected $table = 'ponderaciones';

    protected $fillable = [
        'concepto',
        'porcentaje',
        'color',
        'orden',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje' => 'integer',
            'orden'      => 'integer',
            'status'     => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function historial(): HasMany
    {
        return $this->hasMany(ProyectoPonderacionHistorial::class, 'ponderacion_id');
    }

    /** Devuelve la ponderación activa cuyo porcentaje coincide (o la más cercana). */
    public static function porPorcentaje(int $porcentaje): ?self
    {
        $exacto = static::active()->where('porcentaje', $porcentaje)->first();
        if ($exacto) return $exacto;

        return static::active()
            ->orderByRaw('ABS(porcentaje - ?) ASC', [$porcentaje])
            ->first();
    }
}
