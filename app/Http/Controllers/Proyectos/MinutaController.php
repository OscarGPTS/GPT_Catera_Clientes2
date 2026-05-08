<?php

namespace App\Http\Controllers\Proyectos;

use App\Http\Controllers\Controller;
use App\Models\Proyectos\Proyecto;
use App\Models\Proyectos\MinutaEntrega;
use App\Models\Proyectos\MinutaEntregaParticipante;
use App\Settings\SystemSettings;
use Illuminate\Http\Request;

class MinutaController extends Controller
{
    public function show(Proyecto $proyecto)
    {
        $proyecto->load(['cliente', 'sublinea', 'gerenteProyectos', 'directorDn']);

        $minuta = $proyecto->minutaEntrega;
        $minutaObligatoria = app(SystemSettings::class)->minuta_entrega_obligatoria;

        return view('proyectos.minuta-entrega', [
            'proyectoId' => $proyecto->id,
            'proyecto' => $proyecto,
            'minuta' => $minuta,
            'minutaObligatoria' => $minutaObligatoria,
        ]);
    }

    public function store(Request $request, Proyecto $proyecto)
    {
        $this->authorize('crear minuta');

        if ($proyecto->minutaEntrega) {
            return back()->withErrors(['minuta' => 'Ya existe una minuta para este proyecto.']);
        }

        $validated = $request->validate([
            'fecha_reunion' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
            'modalidad' => 'required|in:presencial,virtual,mixta',
            'orden_del_dia' => 'required|array|min:1',
            'acuerdos' => 'nullable|array',
            'participantes' => 'required|array|min:2',
        ]);

        $minuta = MinutaEntrega::create([
            'proyecto_id' => $proyecto->id,
            'fecha_reunion' => $validated['fecha_reunion'],
            'hora_inicio' => $validated['hora_inicio'],
            'hora_fin' => $validated['hora_fin'],
            'modalidad' => $validated['modalidad'],
            'orden_del_dia' => $validated['orden_del_dia'],
            'acuerdos' => $validated['acuerdos'] ?? [],
            'status' => 'borrador',
        ]);

        foreach ($validated['participantes'] as $participanteId) {
            MinutaEntregaParticipante::create([
                'minuta_id' => $minuta->id,
                'user_id' => $participanteId,
                'rol_en_minuta' => 'participante',
            ]);
        }

        $proyecto->eventos()->create([
            'tipo' => 'minuta_creada',
            'user_id' => auth()->id(),
            'comentario' => 'Minuta de entrega creada (borrador).',
        ]);

        return redirect()->route('proyectos.minuta', $proyecto)
            ->with('success', 'Minuta creada correctamente.');
    }

    public function firmar(Request $request, Proyecto $proyecto)
    {
        $this->authorize('firmar minuta');

        $minuta = $proyecto->minutaEntrega;

        if (! $minuta) {
            return back()->withErrors(['minuta' => 'No existe minuta para este proyecto.']);
        }

        if ($minuta->status === 'firmada') {
            return back()->withErrors(['minuta' => 'La minuta ya está firmada.']);
        }

        $minuta->update([
            'status' => 'firmada',
            'firmado_at' => now(),
        ]);

        $proyecto->eventos()->create([
            'tipo' => 'minuta_firmada',
            'user_id' => auth()->id(),
            'comentario' => 'Minuta de entrega firmada.',
        ]);

        return back()->with('success', 'Minuta firmada correctamente.');
    }
}