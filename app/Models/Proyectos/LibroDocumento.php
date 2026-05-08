<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LibroDocumento extends Model
{
    use HasFactory;

    protected $table = 'libro_documentos';

    protected $fillable = [
        'seccion_id',
        'nombre',
        'archivo_path',
        'version',
        'mime_type',
        'tamaño',
        'subido_por_id',
        'subido_at',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'tamaño' => 'integer',
            'subido_at' => 'datetime',
        ];
    }

    public function seccion(): BelongsTo
    {
        return $this->belongsTo(LibroSeccion::class, 'seccion_id');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'subido_por_id');
    }
}