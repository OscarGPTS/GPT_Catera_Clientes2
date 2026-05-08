<?php

namespace App\Models\Finanzas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CierreSeccion extends Model
{
    use HasFactory;

    protected $table = 'cierres_secciones';

    protected $fillable = [
        'cierre_id',
        'codigo',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    public function cierre(): BelongsTo
    {
        return $this->belongsTo(CierreMensual::class, 'cierre_id');
    }

    public function lineas(): HasMany
    {
        return $this->hasMany(CierreLinea::class, 'seccion_id');
    }
}