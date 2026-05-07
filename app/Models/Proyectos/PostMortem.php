<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMortem extends Model
{
    use HasFactory;

    protected $table = 'post_mortem';

    protected $fillable = [
        'proyecto_id',
        'fecha',
        'resumen',
        'aspectos_positivos',
        'aspectos_negativos',
        'lecciones_aprendidas',
        'conclusiones',
        'recomendaciones',
        'pdf_path',
        'elaborado_por_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'aspectos_positivos' => 'array',
            'aspectos_negativos' => 'array',
            'lecciones_aprendidas' => 'array',
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function elaboradoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'elaborado_por_id');
    }
}
