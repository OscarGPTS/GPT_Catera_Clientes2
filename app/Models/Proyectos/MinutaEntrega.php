<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinutaEntrega extends Model
{
    use HasFactory;

    protected $table = 'minutas_entrega';

    protected $fillable = [
        'proyecto_id',
        'fecha_reunion',
        'hora_inicio',
        'hora_fin',
        'modalidad',
        'orden_del_dia',
        'acuerdos',
        'pdf_path',
        'firmado_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'fecha_reunion' => 'datetime',
            'hora_inicio' => 'datetime',
            'hora_fin' => 'datetime',
            'orden_del_dia' => 'array',
            'acuerdos' => 'array',
            'firmado_at' => 'datetime',
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function participantes()
    {
        return $this->hasMany(MinutaEntregaParticipante::class, 'minuta_id');
    }
}
