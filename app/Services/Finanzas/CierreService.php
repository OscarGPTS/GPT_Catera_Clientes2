<?php

namespace App\Services\Finanzas;

use App\Models\Finanzas\CierreMensual;
use App\Models\Finanzas\CierreSeccion;
use App\Models\Finanzas\CierreLinea;
use App\Models\Proyectos\Proyecto;
use Illuminate\Support\Facades\DB;

class CierreService
{
    public function generarCierreGerencial(int $mes, int $año, int $userId): CierreMensual
    {
        return DB::transaction(function () use ($mes, $año, $userId) {
            $fechaCorte = now()->createFromDate($año, $mes, 1)->endOfMonth();

            $cierre = CierreMensual::updateOrCreate(
                [
                    'mes' => $mes,
                    'año' => $año,
                    'tipo' => 'gerencial_avance',
                ],
                [
                    'fecha_corte' => $fechaCorte->toDateString(),
                    'status' => 'generado',
                    'generado_por_id' => $userId,
                    'observaciones' => null,
                ]
            );

            $cierre->secciones()->delete();

            $facturasSat = $this->calcularSatBase($mes, $año);
            $proyectosDevengado = $this->calcularDevengado($mes, $año);
            $pipelinePonderado = $this->calcularPipelinePonderado($año);

            $seccionSat = $cierre->secciones()->create([
                'codigo' => 'sat_base',
                'total' => $facturasSat['total'],
            ]);
            foreach ($facturasSat['lineas'] as $linea) {
                $seccionSat->lineas()->create([
                    'proyecto_id' => $linea['proyecto_id'] ?? null,
                    'monto' => $linea['monto'],
                    'porcentaje_aplicado' => 100,
                    'observaciones' => $linea['folio'] ?? null,
                ]);
            }

            $totalDevengado = collect($proyectosDevengado)->sum('monto_devengado');
            $seccionDevengado = $cierre->secciones()->create([
                'codigo' => 'devengado',
                'total' => $totalDevengado,
            ]);
            foreach ($proyectosDevengado as $item) {
                $seccionDevengado->lineas()->create([
                    'proyecto_id' => $item['proyecto_id'] ?? null,
                    'monto' => $item['monto_devengado'],
                    'porcentaje_aplicado' => $item['avance_mes'] ?? 100,
                    'observaciones' => ($item['metodo'] ?? '') . ' · OC: ' . ($item['oc_firmada'] ? 'Sí' : 'No'),
                ]);
            }

            $totalPipeline = collect($pipelinePonderado)->sum('monto_ponderado');
            $seccionPipeline = $cierre->secciones()->create([
                'codigo' => 'pipeline_ponderado',
                'total' => $totalPipeline,
            ]);
            foreach ($pipelinePonderado as $item) {
                $seccionPipeline->lineas()->create([
                    'proyecto_id' => $item['proyecto_id'] ?? null,
                    'monto' => $item['monto_ponderado'],
                    'porcentaje_aplicado' => $item['probabilidad'] ?? 100,
                    'observaciones' => ($item['sublinea'] ?? '') . ' · Prob: ' . ($item['probabilidad'] ?? 0) . '%',
                ]);
            }

            return $cierre->load('secciones.lineas.proyecto', 'generadoPor', 'aprobadoPor');
        });
    }

    public function aprobarCierre(int $cierreId, int $userId): CierreMensual
    {
        $cierre = CierreMensual::findOrFail($cierreId);
        $cierre->update([
            'status' => 'aprobado',
            'aprobado_por_id' => $userId,
        ]);
        return $cierre->load('secciones.lineas.proyecto', 'generadoPor', 'aprobadoPor');
    }

    public function getCierreData(int $mes, int $año): array
    {
        $cierre = CierreMensual::where('mes', $mes)
            ->where('año', $año)
            ->where('tipo', 'gerencial_avance')
            ->with('secciones.lineas.proyecto', 'generadoPor', 'aprobadoPor')
            ->first();

        $facturasSat = $this->calcularSatBase($mes, $año);
        $proyectosDevengado = $this->calcularDevengado($mes, $año);
        $pipelinePonderado = $this->calcularPipelinePonderado($año);

        $totalSat = $facturasSat['total'];
        $totalDevengado = collect($proyectosDevengado)->sum('monto_devengado');
        $totalPipeline = collect($pipelinePonderado)->sum('monto_ponderado');
        $totalGerencial = $totalSat + $totalDevengado + $totalPipeline;
        $delta = $totalGerencial - $totalSat;
        $deltaPct = $totalSat > 0 ? round(($delta / $totalSat) * 100, 1) : 0;

        $workflowStatus = 'borrador';
        $generadoPor = '—';
        $fechaGeneracion = '—';
        $revisadoCfo = '—';
        $aprobadoDg = '—';
        $auditEntries = [];

        if ($cierre) {
            $workflowStatus = $cierre->status;
            $generadoPor = $cierre->generadoPor?->name ?? '—';
            $fechaGeneracion = $cierre->created_at?->format('d/m/Y H:i') ?? '—';
            $aprobadoDg = $cierre->aprobadoPor?->name ?? '—';

            $auditEntries[] = [
                'accion' => 'Cierre generado',
                'fecha' => $cierre->created_at?->format('d/m/Y H:i') ?? '—',
                'usuario' => $generadoPor,
            ];
            if ($cierre->aprobado_por_id) {
                $auditEntries[] = [
                    'accion' => 'Cierre aprobado y bloqueado',
                    'fecha' => $cierre->updated_at?->format('d/m/Y H:i') ?? '—',
                    'usuario' => $aprobadoDg,
                ];
            }
        }

        return [
            'cierre' => $cierre,
            'facturas_sat' => $facturasSat['lineas'],
            'total_sat' => $totalSat,
            'proyectos_devengado' => $proyectosDevengado,
            'pipeline_ponderado' => $pipelinePonderado,
            'total_devengado' => $totalDevengado,
            'total_pipeline' => $totalPipeline,
            'total_gerencial' => $totalGerencial,
            'delta' => $delta,
            'delta_pct' => $deltaPct,
            'workflow_status' => $workflowStatus,
            'generado_por' => $generadoPor,
            'fecha_generacion' => $fechaGeneracion,
            'revisado_cfo' => $revisadoCfo,
            'aprobado_dg' => $aprobadoDg,
            'audit_entries' => $auditEntries,
        ];
    }

    protected function calcularSatBase(int $mes, int $año): array
    {
        $proyectosConCotizacion = Proyecto::whereHas('cotizaciones', function ($q) {
            $q->where('status', 'aprobado');
        })
            ->with(['cliente', 'cotizaciones' => function ($q) {
                $q->where('status', 'aprobado');
            }])
            ->get();

        $lineas = [];
        $total = 0;

        foreach ($proyectosConCotizacion as $proyecto) {
            $cotizacion = $proyecto->cotizaciones->first();
            if (!$cotizacion) {
                continue;
            }
            $monto = (float) ($cotizacion->precio_venta_final ?? 0);
            $lineas[] = [
                'proyecto_id' => $proyecto->id,
                'folio' => $proyecto->cp_numero ?? 'CP-000',
                'cliente' => $proyecto->cliente?->razon_social ?? '—',
                'proyecto_dn' => $proyecto->dn_numero ?? $proyecto->cp_numero,
                'fecha' => $cotizacion->fecha_emision ? $cotizacion->fecha_emision->format('d/m/Y') : '—',
                'monto_usd' => $monto,
                'monto' => $monto,
            ];
            $total += $monto;
        }

        return ['lineas' => $lineas, 'total' => $total];
    }

    protected function calcularDevengado(int $mes, int $año): array
    {
        $proyectos = Proyecto::whereIn('estado', ['en_ejecucion', 'en_cierre'])
            ->with(['cliente', 'cotizaciones' => function ($q) {
                $q->where('status', 'aprobado');
            }])
            ->get();

        $result = [];
        foreach ($proyectos as $proyecto) {
            $cotizacion = $proyecto->cotizaciones->first();
            $precioTotal = (float) ($cotizacion?->precio_venta_final ?? 0);
            $avance = 0;
            $metodo = $proyecto->metodo_distribucion_plurianual ?? 'dias_naturales';
            $montoDevengado = $precioTotal * ($avance / 100);

            $result[] = [
                'proyecto_id' => $proyecto->id,
                'dn' => $proyecto->dn_numero ?? $proyecto->cp_numero,
                'cliente' => $proyecto->cliente?->razon_social ?? '—',
                'oc_firmada' => false,
                'avance_mes' => $avance,
                'metodo' => $metodo === 'dias_naturales' ? 'Días naturales' : 'Plurianual',
                'monto_devengado' => $montoDevengado,
            ];
        }

        return $result;
    }

    protected function calcularPipelinePonderado(int $año): array
    {
        $proyectos = Proyecto::whereIn('estado', ['cotizando', 'presentado'])
            ->with(['cliente', 'sublinea', 'cotizaciones'])
            ->get();

        $probabilidades = [
            'cotizando' => 25,
            'presentado' => 50,
        ];

        $result = [];
        foreach ($proyectos as $proyecto) {
            $cotizacion = $proyecto->cotizaciones->first();
            $montoOfertado = (float) ($cotizacion?->precio_venta_final ?? 0);
            $probabilidad = $probabilidades[$proyecto->estado] ?? 10;

            if ($montoOfertado <= 0) {
                continue;
            }

            $result[] = [
                'proyecto_id' => $proyecto->id,
                'cp' => $proyecto->cp_numero ?? '—',
                'cliente' => $proyecto->cliente?->razon_social ?? '—',
                'sublinea' => $proyecto->sublinea?->nombre ?? '—',
                'probabilidad' => $probabilidad,
                'monto_ofertado' => $montoOfertado,
                'monto_ponderado' => $montoOfertado * ($probabilidad / 100),
            ];
        }

        return $result;
    }
}