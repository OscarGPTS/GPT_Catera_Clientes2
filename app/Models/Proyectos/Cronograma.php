<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cronograma extends Model
{
    use HasFactory;

    protected $table = 'cronogramas';

    protected $fillable = [
        'proyecto_id',
        'version',
        'generado_por',
        'archivo_origen_path',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function actividades(): HasMany
    {
        return $this->hasMany(CronogramaActividad::class, 'cronograma_id');
    }
}
