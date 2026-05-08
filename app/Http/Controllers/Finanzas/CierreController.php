<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\Finanzas\CierreMensual;
use Illuminate\Http\Request;

class CierreController extends Controller
{
    public function index(Request $request)
    {
        $ano = $request->input('ano', now()->year);
        $mes = $request->input('mes', now()->month);

        $cierres = CierreMensual::where('ano', $ano)
            ->where('mes', $mes)
            ->with('cerradoPor', 'secciones')
            ->get();

        return view('finanzas.cierres', compact('cierres', 'ano', 'mes'));
    }
}