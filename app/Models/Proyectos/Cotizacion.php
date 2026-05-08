<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'proyecto_id', 'version', 'costo_directo', 'factor_indirectos',
        'factor_admin', 'factor_utilidad', 'precio_venta_calculado',
        'precio_venta_final', 'moneda', 'status', 'generado_por',
        'fecha_emision', 'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'costo_directo' => 'decimal:2',
            'factor_indirectos' => 'decimal:4',
            'factor_admin' => 'decimal:4',
            'factor_utilidad' => 'decimal:4',
            'precio_venta_calculado' => 'decimal:2',
            'precio_venta_final' => 'decimal:2',
            'fecha_emision' => 'date',
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function partidas(): HasMany
    {
        return $this->hasMany(CotizacionPartida::class, 'cotizacion_id');
    }

    public function generadoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'generado_por');
    }
}
