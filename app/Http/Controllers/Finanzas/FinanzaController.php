<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\Finanzas\CuentaBancaria;
use App\Models\Proyectos\Proyecto;
use Illuminate\Http\Request;

class FinanzaController extends Controller
{
    public function index()
    {
        $cuentas = CuentaBancaria::with('estadosCuenta')->where('status', 'activo')->get();

        $saldoTotal = $cuentas->sum('saldo');
        $ingresosMes = $cuentas->flatMap->estadosCuenta
            ->where('tipo', 'abono')
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->sum('monto');
        $egresosMes = $cuentas->flatMap->estadosCuenta
            ->where('tipo', 'cargo')
            ->whereMonth('fecha', now()->month)
            ->whereYear('fecha', now()->year)
            ->sum('monto');
        $porConciliar = $cuentas->flatMap->estadosCuenta->where('conciliado', false)->count();

        return view('finanzas.index', compact('cuentas', 'saldoTotal', 'ingresosMes', 'egresosMes', 'porConciliar'));
    }
}