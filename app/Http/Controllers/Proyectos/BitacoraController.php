<?php

namespace App\Http\Controllers\Proyectos;

use App\Http\Controllers\Controller;
use App\Models\Proyectos\Proyecto;
use App\Models\Proyectos\BitacoraDiaria;
use Illuminate\Http\Request;

class BitacoraController extends Controller
{
    public function index(Request $request)
    {
        $proyectos = Proyecto::whereIn('estado', ['en_ejecucion', 'en_cierre'])
            ->with(['cliente', 'sublinea'])
            ->orderBy('cp_numero')
            ->get();

        $selectedProyecto = null;
        $bitacoras = collect();

        if ($request->filled('proyecto_id')) {
            $selectedProyecto = Proyecto::findOrFail($request->input('proyecto_id'));
            $bitacoras = BitacoraDiaria::where('proyecto_id', $selectedProyecto->id)
                ->with('cargadoPor')
                ->orderBy('fecha', 'desc')
                ->paginate(30);
        }

        return view('proyectos.bitacora', compact('proyectos', 'selectedProyecto', 'bitacoras'));
    }

    public function store(Request $request)
    {
        $this->authorize('crear bitacora');

        $validated = $request->validate([
            'proyecto_id' => 'required|exists:proyectos,id',
            'fecha' => 'required|date',
            'relacion_actividades' => 'required|string|max:10000',
            'personal_gpt' => 'nullable|array',
            'equipos_en_sitio' => 'nullable|array',
            'proveedores_subcontratistas' => 'nullable|array',
        ]);

        $validated['cargado_por_id'] = auth()->id();

        $existing = BitacoraDiaria::where('proyecto_id', $validated['proyecto_id'])
            ->where('fecha', $validated['fecha'])
            ->first();

        if ($existing) {
            return back()->withErrors(['fecha' => 'Ya existe una bitácora para esta fecha.']);
        }

        BitacoraDiaria::create($validated);

        return back()->with('success', 'Bitácora registrada correctamente.');
    }
}