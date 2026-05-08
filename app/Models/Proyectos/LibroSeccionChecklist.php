<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibroSeccionChecklist extends Model
{
    use HasFactory;

    protected $table = 'libro_seccion_checklist';

    protected $fillable = [
        'seccion_id',
        'item_descripcion',
        'completado',
        'evidencia_documento_id',
        'completado_por_id',
        'completado_at',
    ];

    protected function casts(): array
    {
        return [
            'completado' => 'boolean',
            'completado_at' => 'datetime',
        ];
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(LibroSeccion::class, 'seccion_id');
    }

    public function completadoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'completado_por_id');
    }
}