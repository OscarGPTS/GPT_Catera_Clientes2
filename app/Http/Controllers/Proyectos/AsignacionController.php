<?php

namespace App\Http\Controllers\Proyectos;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Asignaciones\SnapshotService;
use Illuminate\Http\Request;

class AsignacionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('ver asignaciones');

        $mes = $request->input('mes', now()->month);
        $año = $request->input('ano', now()->year);

        $snapshotService = new SnapshotService();
        $data = $snapshotService->getSnapshotData($mes, $año);

        return view('proyectos.asignaciones', array_merge($data, [
            'mes' => $mes,
            'anio' => $año,
            'trimestre' => ceil($mes / 3),
        ]));
    }

    public function generarSnapshot(Request $request)
    {
        $this->authorize('admin');

        $mes = $request->input('mes', now()->month);
        $año = $request->input('ano', now()->year);

        $snapshotService = new SnapshotService();
        $snapshotService->generar($mes, $año);

        return back()->with('success', 'Snapshot generado correctamente.');
    }

    public function miAsignacion()
    {
        $user = auth()->user();

        $proyectosAsignados = \App\Models\Proyectos\Proyecto::where(function ($q) use ($user) {
            $q->where('gerente_proyectos_id', $user->id)
                ->orWhere('ingeniero_proyectos_id', $user->id)
                ->orWhere('ingeniero_costos_id', $user->id)
                ->orWhere('director_dn_id', $user->id)
                ->orWhere('trainee_id', $user->id);
        })
            ->whereIn('estado', ['en_ejecucion', 'en_cierre', 'adjudicado_firmado', 'cotizando', 'presentado'])
            ->with(['cliente', 'sublinea'])
            ->get();

        return view('perfil.asignacion', compact('user', 'proyectosAsignados'));
    }
}