<?php

namespace App\Livewire\Finanzas;

use App\Models\Finanzas\CuentaBancaria;
use Livewire\Component;

class FinanzasIndex extends Component
{
    public function render()
    {
        $cuentas = CuentaBancaria::with('estadosCuenta')->where('activa', true)->get();

        $saldoTotal = $cuentas->sum('saldo');

        $estados = $cuentas->flatMap->estadosCuenta;

        $ingresosMes = $estados
            ->where('tipo', 'abono')
            ->filter(fn($e) => $e->fecha && $e->fecha->month === now()->month && $e->fecha->year === now()->year)
            ->sum('monto');

        $egresosMes = $estados
            ->where('tipo', 'cargo')
            ->filter(fn($e) => $e->fecha && $e->fecha->month === now()->month && $e->fecha->year === now()->year)
            ->sum('monto');

        $porConciliar = $estados->where('conciliado', false)->count();

        return view('livewire.finanzas.finanzas-index', [
            'cuentas' => $cuentas,
            'saldoTotal' => $saldoTotal,
            'ingresosMes' => $ingresosMes,
            'egresosMes' => $egresosMes,
            'porConciliar' => $porConciliar,
        ])->layout('components.layouts.app');
    }
}