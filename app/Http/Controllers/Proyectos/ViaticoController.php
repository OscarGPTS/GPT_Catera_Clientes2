<?php

namespace App\Http\Controllers\Proyectos;

use App\Http\Controllers\Controller;
use App\Models\Proyectos\Proyecto;
use App\Models\Proyectos\SolicitudViatico;
use Illuminate\Http\Request;

class ViaticoController extends Controller
{
    public function index(Request $request)
    {
        $query = SolicitudViatico::with(['proyecto.cliente', 'solicitante', 'aprobadoPor']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('proyecto_id')) {
            $query->where('proyecto_id', $request->input('proyecto_id'));
        }

        $solicitudes = $query->orderBy('created_at', 'desc')->paginate(20);
        $proyectos = Proyecto::whereIn('estado', ['en_ejecucion', 'en_cierre', 'adjudicado_firmado'])
            ->with('cliente')
            ->orderBy('cp_numero')
            ->get();

        return view('proyectos.viaticos', compact('solicitudes', 'proyectos'));
    }

    public function store(Request $request)
    {
        $this->authorize('solicitar viaticos');

        $validated = $request->validate([
            'proyecto_id' => 'required|exists:proyectos,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'destino' => 'required|string|max:255',
            'motivo' => 'required|string|max:2000',
        ]);

        SolicitudViatico::create([
            ...$validated,
            'solicitante_id' => auth()->id(),
            'status' => 'pendiente_serv_grales',
        ]);

        return back()->with('success', 'Solicitud de viáticos creada.');
    }

    public function aprobar(Request $request, SolicitudViatico $viatico)
    {
        $this->authorize('aprobar viaticos servicios generales');

        $viatico->update([
            'status' => 'pendiente_direccion',
            'aprobado_por_id' => auth()->id(),
            'aprobado_at' => now(),
        ]);

        return back()->with('success', 'Viáticos aprobados por Servicios Generales.');
    }

    public function aprobarDireccion(Request $request, SolicitudViatico $viatico)
    {
        $this->authorize('aprobar viaticos direccion');

        $viatico->update([
            'status' => 'aprobado',
            'aprobado_por_id' => auth()->id(),
            'aprobado_at' => now(),
        ]);

        return back()->with('success', 'Viáticos aprobados por Dirección.');
    }

    public function rechazar(Request $request, SolicitudViatico $viatico)
    {
        $validated = $request->validate([
            'motivo_rechazo' => 'required|string|max:500',
        ]);

        $viatico->update([
            'status' => 'rechazado',
        ]);

        return back()->with('success', 'Solicitud rechazada.');
    }
}