<?php

namespace App\Models\Finanzas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CuentaBancaria extends Model
{
    use HasFactory;

    protected $table = 'cuentas_bancarias';

    protected $fillable = [
        'banco',
        'numero_cuenta',
        'tipo',
        'moneda',
        'saldo_actual',
        'saldo_contable',
        'status',
        'ultima_conciliacion_at',
    ];

    protected function casts(): array
    {
        return [
            'saldo_actual' => 'decimal:2',
            'saldo_contable' => 'decimal:2',
            'ultima_conciliacion_at' => 'datetime',
        ];
    }

    public function estadosCuenta(): HasMany
    {
        return $this->hasMany(EstadoCuenta::class, 'cuenta_bancaria_id');
    }
}
