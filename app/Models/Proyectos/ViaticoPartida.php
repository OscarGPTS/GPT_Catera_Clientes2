<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViaticoPartida extends Model
{
    use HasFactory;

    protected $table = 'viaticos_partidas';

    protected $fillable = [
        'solicitud_viatico_id',
        'concepto',
        'monto_estimado',
        'monto_real',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'monto_estimado' => 'decimal:2',
            'monto_real' => 'decimal:2',
        ];
    }

    public function solicitudViatico(): BelongsTo
    {
        return $this->belongsTo(SolicitudViatico::class, 'solicitud_viatico_id');
    }
}