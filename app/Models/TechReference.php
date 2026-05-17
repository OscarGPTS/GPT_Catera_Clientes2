<?php

namespace App\Models;

use App\Models\Comercial\Cliente;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechReference extends Model
{
    protected $table = 'tech_references';

    protected $fillable = [
        'cliente_id',
        'tech_reference',
        'fecha_referencia',
        'cp_numero',
        'revision',
        'contacto',
        'estado_cliente',
        'pais',
        'zona',
        'nombre_proyecto_cliente',
        'core_business',
        'pipe_in',
        'branch_in',
        'descripcion_larga',
        'amount_usd',
        'amount_mxn',
        'quotation_personnel',
        'account_manager',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'revision'   => 'integer',
            'pipe_in'    => 'decimal:2',
            'branch_in'  => 'decimal:2',
            'amount_usd' => 'decimal:2',
            'amount_mxn' => 'decimal:2',
            'status'     => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
