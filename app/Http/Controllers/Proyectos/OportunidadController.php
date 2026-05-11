<?php

namespace App\Http\Controllers\Proyectos;

use App\Http\Controllers\Controller;
use App\Models\Proyectos\Proyecto;
use Illuminate\Http\Request;

class OportunidadController extends Controller
{
    public function aprobar(Request $request, Proyecto $proyecto)
    {
        $this->authorize('aprobar cp');

        $validated = $request->validate([
            'gerente_proyectos_id' => 'required|exists:users,id',
            'notas' => 'nullable|string|max:5000',
        ]);

        $proyecto->update([
            'estado' => 'cotizando',
            'gerente_proyectos_id' => $validated['gerente_proyectos_id'],
            'notas' => ($proyecto->notas ? $proyecto->notas . "\n" : '') . 'CP aprobado: ' . ($validated['notas'] ?? ''),
        ]);

        $proyecto->eventos()->create([
            'tipo' => 'cp_aprobado',
            'user_id' => auth()->id(),
            'comentario' => 'CP aprobado por Comité Comercial. Gerente asignado: ' . $proyecto->gerenteProyectos->name,
        ]);

        return back()->with('success', 'CP aprobado correctamente.');
    }

    public function rechazar(Request $request, Proyecto $proyecto)
    {
        $this->authorize('aprobar cp');

        $validated = $request->validate([
            'notas' => 'required|string|max:5000',
        ]);

        $proyecto->update([
            'estado' => 'perdido',
            'notas' => ($proyecto->notas ? $proyecto->notas . "\n" : '') . 'CP rechazado: ' . $validated['notas'],
        ]);

        $proyecto->eventos()->create([
            'tipo' => 'cp_rechazado',
            'user_id' => auth()->id(),
            'comentario' => 'CP rechazado: ' . $validated['notas'],
        ]);

        return back()->with('success', 'CP rechazado.');
    }

    public function asignarEquipo(Request $request, Proyecto $proyecto)
    {
        $this->authorize('asignar cp');

        $validated = $request->validate([
            'gerente_proyectos_id' => 'nullable|exists:users,id',
            'ingeniero_costos_id' => 'nullable|exists:users,id',
            'ingeniero_proyectos_id' => 'nullable|exists:users,id',
            'trainee_id' => 'nullable|exists:users,id',
            'gerente_operaciones_id' => 'nullable|exists:users,id',
        ]);

        $proyecto->update($validated);

        $proyecto->eventos()->create([
            'tipo' => 'equipo_asignado',
            'user_id' => auth()->id(),
            'comentario' => 'Equipo asignado al CP.',
        ]);

        return back()->with('success', 'Equipo asignado correctamente.');
    }

    public function cambiarEstado(Request $request, Proyecto $proyecto)
    {
        $validated = $request->validate([
            'estado' => 'required|in:en_revision,cotizando,cotizado,presentado,adjudicado_pendiente,adjudicado_firmado,en_ejecucion,en_cierre,cerrado,cancelado,perdido,archivado',
            'notas' => 'nullable|string|max:5000',
        ]);

        $estadoAnterior = $proyecto->estado;
        $proyecto->update(['estado' => $validated['estado']]);

        $proyecto->eventos()->create([
            'tipo' => 'cambio_estado',
            'user_id' => auth()->id(),
            'comentario' => "Estado cambiado de {$estadoAnterior} a {$validated['estado']}" . ($validated['notas'] ? ': ' . $validated['notas'] : ''),
            'metadata' => ['estado_anterior' => $estadoAnterior, 'estado_nuevo' => $validated['estado']],
        ]);

        return back()->with('success', 'Estado actualizado.');
    }
}