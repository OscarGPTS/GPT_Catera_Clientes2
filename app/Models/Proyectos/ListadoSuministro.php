<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ListadoSuministro extends Model
{
    use HasFactory;

    protected $table = 'listados_suministros';

    protected $fillable = [
        'proyecto_id',
        'porcentaje_avance_global',
    ];

    protected function casts(): array
    {
        return [
            'porcentaje_avance_global' => 'decimal:2',
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ListadoSuministroItem::class, 'listado_suministro_id');
    }
}
