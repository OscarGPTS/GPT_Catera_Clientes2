<?php

namespace App\Http\Controllers\Proyectos;

use App\Http\Controllers\Controller;
use App\Models\Proyectos\Proyecto;
use App\Models\Proyectos\ListadoSuministro;
use Illuminate\Http\Request;

class SuministroController extends Controller
{
    public function index(Request $request)
    {
        $proyectos = Proyecto::whereIn('estado', ['en_ejecucion', 'en_cierre'])
            ->with(['cliente', 'sublinea'])
            ->orderBy('cp_numero')
            ->paginate(20);

        $selectedProyecto = null;
        $listado = null;

        if ($request->filled('proyecto_id')) {
            $selectedProyecto = Proyecto::findOrFail($request->input('proyecto_id'));
            $listado = ListadoSuministro::where('proyecto_id', $selectedProyecto->id)
                ->with('items')
                ->first();
        }

        return view('proyectos.suministros', compact('proyectos', 'selectedProyecto', 'listado'));
    }
}