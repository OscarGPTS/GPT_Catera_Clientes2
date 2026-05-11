<?php

namespace App\Models\Finanzas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CierreMensual extends Model
{
    use HasFactory;

    protected $table = 'cierres_mensuales';

    protected $fillable = [
        'anio',
        'mes',
        'tipo',
        'fecha_corte',
        'status',
        'generado_por_id',
        'aprobado_por_id',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'anio' => 'integer',
            'mes' => 'integer',
            'fecha_corte' => 'date',
        ];
    }

    public function generadoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'generado_por_id');
    }

    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'aprobado_por_id');
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(CierreSeccion::class, 'cierre_id');
    }
}
