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
        'año',
        'mes',
        'fecha_inicio',
        'fecha_fin',
        'status',
        'cerrado_por_id',
        'cerrado_at',
        'total_ingresos',
        'total_egresos',
        'diferencia',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'año' => 'integer',
            'mes' => 'integer',
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'cerrado_at' => 'datetime',
            'total_ingresos' => 'decimal:2',
            'total_egresos' => 'decimal:2',
            'diferencia' => 'decimal:2',
        ];
    }

    public function cerradoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'cerrado_por_id');
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(CierreMensualSeccion::class, 'cierre_mensual_id');
    }
}
