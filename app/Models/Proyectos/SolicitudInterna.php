<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudInterna extends Model
{
    protected $table = 'solicitudes_internas';

    protected $fillable = [
        'tipo', 'proyecto_id', 'cp_numero', 'codigo_formato', 'estado',
        'solicitante_id', 'asignado_id', 'fecha_solicitud',
        'fecha_respuesta_requerida', 'fecha_respuesta_real',
    ];

    protected function casts(): array
    {
        return [
            'fecha_solicitud' => 'date',
            'fecha_respuesta_requerida' => 'date',
            'fecha_respuesta_real' => 'date',
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

    public function items()
    {
        return $this->hasMany(SolicitudInternaItem::class, 'solicitud_id');
    }
}

class SolicitudInternaItem extends Model
{
    protected $table = 'solicitudes_internas_items';

    protected $fillable = [
        'solicitud_id', 'descripcion', 'cantidad', 'unidad',
        'especificacion', 'observaciones',
    ];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudInterna::class, 'solicitud_id');
    }
}
