<?php

namespace App\Http\Controllers\Proyectos;

use App\Http\Controllers\Controller;
use App\Models\Proyectos\Proyecto;
use Illuminate\Http\Request;

class ProyectoController extends Controller
{
    public function index(Request $request)
    {
        $query = Proyecto::with(['cliente', 'sublinea', 'gerenteProyectos'])
            ->whereIn('estado', ['adjudicado_firmado', 'en_ejecucion', 'en_cierre']);

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function ($q) use ($buscar) {
                $q->where('cp_numero', 'ilike', "%{$buscar}%")
                    ->orWhere('dn_numero', 'ilike', "%{$buscar}%")
                    ->orWhere('tech_reference', 'ilike', "%{$buscar}%")
                    ->orWhereHas('cliente', fn($c) => $c->where('razon_social', 'ilike', "%{$buscar}%"));
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->input('estado'));
        }

        if ($request->filled('sublinea')) {
            $query->where('sublinea_id', $request->input('sublinea'));
        }

        $proyectos = $query->latest()->paginate(20)->withQueryString();

        return view('proyectos.index', compact('proyectos'));
    }

    public function show(Proyecto $proyecto)
    {
        return redirect()->route('oportunidades.show', $proyecto);
    }
}