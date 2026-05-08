<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViaticoPersonal extends Model
{
    use HasFactory;

    protected $table = 'viaticos_personal';

    protected $fillable = [
        'solicitud_viatico_id',
        'user_id',
        'dias',
    ];

    protected function casts(): array
    {
        return [
            'dias' => 'integer',
        ];
    }

    public function solicitudViatico(): BelongsTo
    {
        return $this->belongsTo(SolicitudViatico::class, 'solicitud_viatico_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}