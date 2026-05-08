<?php

namespace App\Http\Controllers\Proyectos;

use App\Http\Controllers\Controller;
use App\Models\Proyectos\Proyecto;
use Illuminate\Http\Request;

class AdjudicacionController extends Controller
{
    public function show(Proyecto $proyecto)
    {
        $this->authorize('adjudicar', $proyecto);

        $proyecto->load(['cliente.contactos', 'sublinea', 'gerenteProyectos', 'cotizaciones.partidas']);

        return view('proyectos.adjudicacion', compact('proyecto'));
    }

    public function adjudicar(Request $request, Proyecto $proyecto)
    {
        $this->authorize('adjudicar', $proyecto);

        $validated = $request->validate([
            'fecha_inicio_planeada' => 'required|date',
            'fecha_fin_planeada' => 'required|date|after:fecha_inicio_planeada',
            'metodo_distribucion_plurianual' => 'required|in:dias_naturales,hitos',
            'gerente_proyectos_id' => 'required|exists:users,id',
            'gerente_operaciones_id' => 'nullable|exists:users,id',
            'notas' => 'nullable|string|max:5000',
        ]);

        $proyecto->update([
            'estado' => 'adjudicado_pendiente',
            'fecha_inicio_planeada' => $validated['fecha_inicio_planeada'],
            'fecha_fin_planeada' => $validated['fecha_fin_planeada'],
            'metodo_distribucion_plurianual' => $validated['metodo_distribucion_plurianual'],
            'gerente_proyectos_id' => $validated['gerente_proyectos_id'],
            'gerente_operaciones_id' => $validated['gerente_operaciones_id'],
            'notas' => ($proyecto->notas ? $proyecto->notas . "\n" : '') . 'Adjudicado: ' . ($validated['notas'] ?? ''),
        ]);

        $proyecto->eventos()->create([
            'tipo' => 'adjudicado',
            'user_id' => auth()->id(),
            'comentario' => 'Proyecto adjudicado. GP asignado: ' . $proyecto->gerenteProyectos->name,
        ]);

        return redirect()->route('oportunidades.show', $proyecto)
            ->with('success', 'Proyecto adjudicado correctamente. Pendiente de firma.');
    }

    public function firmar(Request $request, Proyecto $proyecto)
    {
        $this->authorize('adjudicar', $proyecto);

        $validated = $request->validate([
            'notas' => 'nullable|string|max:5000',
        ]);

        $proyecto->update([
            'estado' => 'adjudicado_firmado',
            'notas' => ($proyecto->notas ? $proyecto->notas . "\n" : '') . 'Firma confirmada: ' . ($validated['notas'] ?? ''),
        ]);

        $proyecto->eventos()->create([
            'tipo' => 'firma_adjudicacion',
            'user_id' => auth()->id(),
            'comentario' => 'Firma de adjudicación confirmada.',
        ]);

        return redirect()->route('oportunidades.show', $proyecto)
            ->with('success', 'Firma de adjudicación confirmada. Proyecto listo para ejecución.');
    }
}