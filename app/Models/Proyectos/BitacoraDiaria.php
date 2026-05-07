<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraDiaria extends Model
{
    use HasFactory;

    protected $table = 'bitacora_diaria';

    protected $fillable = [
        'proyecto_id',
        'fecha',
        'relacion_actividades',
        'personal_gpt',
        'equipos_en_sitio',
        'proveedores_subcontratistas',
        'vobo_cliente_nombre',
        'vobo_cliente_organizacion',
        'vobo_cliente_fecha',
        'vobo_cliente_firma_path',
        'cargado_por_id',
        'firmado_at',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'personal_gpt' => 'array',
            'equipos_en_sitio' => 'array',
            'proveedores_subcontratistas' => 'array',
            'vobo_cliente_fecha' => 'datetime',
            'firmado_at' => 'datetime',
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function cargadoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'cargado_por_id');
    }
}
