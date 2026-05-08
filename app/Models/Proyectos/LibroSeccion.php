<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibroSeccion extends Model
{
    use HasFactory;

    protected $table = 'libro_secciones';

    protected $fillable = [
        'libro_id',
        'codigo',
        'nombre',
        'descripcion',
        'porcentaje_avance',
        'estado',
        'responsable_id',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje_avance' => 'integer',
        ];
    }

    public function libro(): BelongsTo
    {
        return $this->belongsTo(LibroProyecto::class, 'libro_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'responsable_id');
    }

    public function checklist(): HasMany
    {
        return $this->hasMany(LibroSeccionChecklist::class, 'seccion_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(LibroDocumento::class, 'seccion_id');
    }
}