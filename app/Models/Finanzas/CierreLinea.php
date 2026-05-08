<?php

namespace App\Models\Finanzas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CierreLinea extends Model
{
    use HasFactory;

    protected $table = 'cierres_lineas';

    protected $fillable = [
        'seccion_id',
        'proyecto_id',
        'monto',
        'porcentaje_aplicado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'porcentaje_aplicado' => 'decimal:2',
        ];
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(CierreSeccion::class, 'seccion_id');
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Proyectos\Proyecto::class, 'proyecto_id');
    }
}