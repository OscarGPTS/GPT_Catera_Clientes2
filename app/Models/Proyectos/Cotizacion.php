<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cotizacion extends Model
{
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
}

class CotizacionPartida extends Model
{
    protected $table = 'cotizacion_partidas';

    protected $fillable = [
        'cotizacion_id', 'numero_partida', 'descripcion', 'cantidad',
        'unidad', 'costo_unitario', 'costo_total', 'observaciones',
    ];

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }
}
