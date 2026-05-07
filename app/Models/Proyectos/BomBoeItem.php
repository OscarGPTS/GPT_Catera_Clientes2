<?php

namespace App\Models\Proyectos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomBoeItem extends Model
{
    use HasFactory;

    protected $table = 'bom_boe_items';

    protected $fillable = [
        'proyecto_id',
        'tipo',
        'descripcion',
        'cantidad',
        'unidad',
        'status',
        'responsable_id',
        'fecha_requerida',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'fecha_requerida' => 'datetime',
        ];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'responsable_id');
    }
}
