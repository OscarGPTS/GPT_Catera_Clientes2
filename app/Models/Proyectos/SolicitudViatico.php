<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SolicitudViatico extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_viaticos';

    protected $fillable = [
        'proyecto_id',
        'fecha_solicitud',
        'fecha_inicio',
        'fecha_fin',
        'destino',
        'motivo',
        'status',
        'solicitante_id',
        'aprobado_por_id',
        'aprobado_at',
        'observaciones',
        'monto_total',
    ];

    protected function casts(): array
    {
        return [
            'fecha_solicitud' => 'datetime',
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'aprobado_at' => 'datetime',
            'monto_total' => 'decimal:2',
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'solicitante_id');
    }

    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'aprobado_por_id');
    }

    public function viaticosPersonal(): HasMany
    {
        return $this->hasMany(ViaticoPersonal::class, 'solicitud_viatico_id');
    }

    public function viaticosPartidas(): HasMany
    {
        return $this->hasMany(ViaticoPartida::class, 'solicitud_viatico_id');
    }
}
