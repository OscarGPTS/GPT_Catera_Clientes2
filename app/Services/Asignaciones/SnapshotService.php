<?php

namespace App\Services\Asignaciones;

use App\Models\Proyectos\AsignacionSnapshot;
use App\Models\Proyectos\Proyecto;
use App\Models\User;

class SnapshotService
{
    public function generar(int $mes, int $anio): void
    {
        $equipo = User::role(['gerente_proyectos', 'ingeniero_proyectos', 'ingeniero_costos', 'trainee_proyectos', 'gerente_operaciones', 'director_dn'])
            ->where('status', 'active')
            ->get();

        foreach ($equipo as $persona) {
            $asignacionesProyecto = Proyecto::where(function ($q) use ($persona) {
                $q->where('gerente_proyectos_id', $persona->id)
                    ->orWhere('ingeniero_proyectos_id', $persona->id)
                    ->orWhere('ingeniero_costos_id', $persona->id)
                    ->orWhere('director_dn_id', $persona->id)
                    ->orWhere('trainee_id', $persona->id);
            })
                ->whereIn('estado', ['en_ejecucion', 'en_cierre', 'adjudicado_firmado'])
                ->get();

            $proyectosActivos = $asignacionesProyecto->count();
            $cpAsignados = $asignacionesProyecto->where('estado', '!=', 'cerrado')->where('estado', '!=', 'cancelado')->where('estado', '!=', 'perdido')->count();
            $dnActivos = $asignacionesProyecto->filter(fn($p) => !empty($p->dn_numero))->count();

            $dnStandBy = $asignacionesProyecto->where('estado', 'en_revision')->count();
            $dnCerrados = $asignacionesProyecto->whereIn('estado', ['cerrado'])->count();
            $dnCancelados = $asignacionesProyecto->whereIn('estado', ['cancelado', 'perdido'])->count();

            $totalServicio = 0;
            $totalSuministro = 0;

            foreach ($asignacionesProyecto as $p) {
                $precio = (float) ($p->cotizaciones->first()->precio_venta_final ?? 0);
                if ($p->metodo_distribucion_plurianual !== 'dias_naturales') {
                    $totalServicio += $precio;
                } else {
                    $totalSuministro += $precio;
                }
            }

            $cerradosQ = $dnCerrados;

            $snapshot = AsignacionSnapshot::firstOrNew([
                'user_id' => $persona->id,
                'mes' => $mes,
                'anio' => $anio,
            ]);

            $snapshot->fill([
                'cp_asignados' => $cpAsignados,
                'cp_ejecutados' => 0,
                'cp_remanentes' => 0,
                'cp_residual_anterior' => 0,
                'dn_activos' => $dnActivos,
                'dn_stand_by' => $dnStandBy,
                'dn_cerrados' => $dnCerrados,
                'dn_cancelados' => $dnCancelados,
                'total_servicio' => $totalServicio,
                'total_suministro' => $totalSuministro,
                'gerencia_regional' => $this->mapDepartamentoToGerencia($persona->departamento),
                'generado_at' => now(),
            ]);
            $snapshot->save();
        }
    }

    public function getSnapshotData(int $mes, int $anio): array
    {
        $snapshots = AsignacionSnapshot::where('mes', $mes)
->where('anio', $anio)
            ->get();

        $users = User::role(['gerente_proyectos', 'ingeniero_proyectos', 'ingeniero_costos', 'trainee_proyectos', 'gerente_operaciones', 'director_dn'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $allYearSnapshots = AsignacionSnapshot::where('anio', $anio)
            ->whereIn('user_id', $users->keys())
            ->get()
            ->groupBy('user_id');

        $asignaciones = [];
        foreach ($users as $userId => $user) {
            $snapshot = $snapshots->firstWhere('user_id', $userId);
            $userSnapshots = $allYearSnapshots->get($userId, collect());

            $meses = [];
            for ($m = 1; $m <= 12; $m++) {
                $mSnapshot = $userSnapshots->firstWhere('mes', $m);
                $meses[] = $mSnapshot?->cp_asignados ?? null;
            }

            $promedio = collect($meses)->filter()->avg();

            $asignaciones[] = [
                'nombre' => $user->name,
                'puesto' => $user->puesto ?? $user->roles->first()?->name ?? '—',
                'gerencia' => $snapshot?->gerencia_regional ?? $user->departamento ?? '—',
                'meses' => $meses,
                'promedio' => $promedio ? number_format($promedio, 1) : '—',
                'cp_asignados' => $snapshot?->cp_asignados ?? 0,
                'cp_ejecutados' => $snapshot?->cp_ejecutados ?? 0,
                'cp_remanentes' => $snapshot?->cp_remanentes ?? 0,
                'residual' => $snapshot?->cp_residual_anterior ?? 0,
                'dn_activos' => $snapshot?->dn_activos ?? 0,
                'dn_totales' => ($snapshot?->dn_activos ?? 0) + ($snapshot?->dn_stand_by ?? 0) + ($snapshot?->dn_cerrados ?? 0) + ($snapshot?->dn_cancelados ?? 0),
                'servicio' => $snapshot?->total_servicio ?? 0,
                'suministro' => $snapshot?->total_suministro ?? 0,
                'cerrados_q' => $snapshot?->dn_cerrados ?? 0,
                'carga_actual' => $snapshot?->cp_asignados ?? 0,
                'puesto_rh' => $user->puesto ?? '—',
                'fecha_ingreso' => $user->created_at?->format('d/m/Y') ?? '—',
                'formacion' => '—',
                'antiguedad' => $user->created_at?->diffInYears(now()) . ' años',
            ];
        }

        $equipoTotal = $users->count();
        $proyectosActivos = Proyecto::whereIn('estado', ['en_ejecucion', 'en_cierre'])->count();
        $cargaPromedio = collect($asignaciones)->filter(fn($a) => $a['carga_actual'] > 0)->avg('carga_actual');
        $sobrecarga = collect($asignaciones)->filter(fn($a) => $a['carga_actual'] >= 10)->count();

        return [
            'asignaciones' => $asignaciones,
            'equipo_total' => $equipoTotal,
            'proyectos_activos' => $proyectosActivos,
            'carga_promedio' => $cargaPromedio ? number_format($cargaPromedio, 1) : '0',
            'sobrecarga' => $sobrecarga,
            'snapshot_generado' => $snapshots->count() > 0,
        ];
    }

    protected function mapDepartamentoToGerencia(?string $departamento): ?string
    {
        $map = [
            'Proyectos' => null,
            'Comercial' => null,
            'Finanzas' => null,
            'Dirección' => 'DG',
            'Operaciones' => null,
            'QHSE' => null,
        ];

        return $map[$departamento] ?? null;
    }
}