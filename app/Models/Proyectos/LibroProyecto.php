<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibroProyecto extends Model
{
    use HasFactory;

    protected $table = 'libros_proyecto';

    protected $fillable = [
        'proyecto_id',
        'fecha_apertura',
        'fecha_cierre_estimado',
        'fecha_cierre_real',
        'porcentaje_avance_global',
        'bloqueado_para_cierre',
    ];

    protected function casts(): array
    {
        return [
            'fecha_apertura' => 'date',
            'fecha_cierre_estimado' => 'date',
            'fecha_cierre_real' => 'date',
            'porcentaje_avance_global' => 'integer',
            'bloqueado_para_cierre' => 'boolean',
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function secciones(): HasMany
    {
        return $this->hasMany(LibroSeccion::class, 'libro_id');
    }
}