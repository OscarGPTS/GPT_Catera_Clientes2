<?php

namespace App\Livewire\Ejecutivo;

use App\Models\Comercial\Cliente;
use App\Models\Comercial\Sublinea;
use App\Models\Proyectos\Proyecto;
use Livewire\Component;

class DashboardIndex extends Component
{
    public $periodo = 'trimestre';
    public $clientesSeleccionados = [];
    public $sublineasSeleccionadas = [];
    public $incluirSedena = true;

    public function mount()
    {
        $this->periodo = request()->query('periodo', 'trimestre');
    }

    public function updatingPeriodo()
    {
        $this->resetPage();
    }

    public function getClientesProperty()
    {
        return Cliente::where('activo', true)->orderBy('razon_social')->get();
    }

    public function getSublineasProperty()
    {
        return Sublinea::orderBy('codigo')->get();
    }

    public function getDashboardDataProperty()
    {
        $query = Proyecto::with(['cliente', 'sublinea', 'gerenteProyectos', 'cotizaciones']);

        if (!empty($this->sublineasSeleccionadas)) {
            $query->whereIn('sublinea_id', $this->sublineasSeleccionadas);
        }

        if (!empty($this->clientesSeleccionados)) {
            $query->whereIn('cliente_id', $this->clientesSeleccionados);
        }

        if (!$this->incluirSedena) {
            $query->whereDoesntHave('cliente', fn($q) => $q->whereRaw('LOWER(razon_social) LIKE ?', ['%sedena%']));
        }

        $pipeline = $query->get();
        $pipelineTotal = $pipeline->count();

        $estadosAdjudicados = ['adjudicado_pendiente', 'adjudicado_firmado', 'en_ejecucion', 'en_cierre', 'cerrado'];
        $adjudicadosCount = $pipeline->whereIn('estado', $estadosAdjudicados)->count();

        $hitRateConteo = $pipelineTotal > 0 ? round(($adjudicadosCount / $pipelineTotal) * 100, 1) : 0;

        $concentracionSedena = $pipelineTotal > 0
            ? round(($pipeline->filter(fn($p) => $p->cliente && str_contains(strtolower($p->cliente->razon_social), 'sedena'))->count() / $pipelineTotal) * 100, 1)
            : 0;

        $porSublinea = $pipeline->groupBy(fn($p) => $p->sublinea?->nombre ?? 'Sin línea')->map->count()->sortDesc();
        $porEstado = $pipeline->groupBy('estado')->map->count();
        $porCliente = $pipeline->groupBy(fn($p) => $p->cliente?->razon_social ?? 'Sin cliente')->map->count()->sortDesc();

        $oportunidadesAtencion = Proyecto::whereIn('estado', ['en_revision', 'cotizando', 'presentado'])
            ->where('updated_at', '<', now()->subDays(7))
            ->with(['cliente', 'sublinea', 'gerenteProyectos', 'directorDn', 'cotizaciones', 'miembros'])
            ->orderBy('updated_at')
            ->take(5)
            ->get()
            ->map(fn($p) => (object) [
                'id' => $p->id,
                'cp' => $p->cp_numero,
                'ref_tecnica' => $p->tech_reference,
                'cliente' => $p->cliente,
                'estado' => $p->estado,
                'monto_estimado' => $p->cotizaciones->max('precio_venta_final') ?? 0,
                'dias_sin_actividad' => $p->updated_at->diffInDays(now()),
                'lider' => $p->gerenteProyectos ?? $p->directorDn,
                'accion_sugerida' => match($p->estado) { 'cotizando' => 'Cotizar', 'presentado' => 'Presentar', default => 'Dar seguimiento' },
            ]);

        $gerentes = $pipeline
            ->filter(fn($p) => $p->miembros->firstWhere('rol', 'gerente_proyectos') !== null)
            ->groupBy(fn($p) => $p->miembros->firstWhere('rol', 'gerente_proyectos')?->user_id);
        $cargaEquipo = [
            'avg' => $gerentes->count() > 0 ? round($gerentes->map->count()->avg(), 1) : 0,
            'sobrecarga' => $gerentes->map->count()->filter(fn($c) => $c > 6)->count(),
            'total' => $gerentes->count(),
        ];

        $dossiersRiesgo = Proyecto::where('estado', 'en_ejecucion')->whereDoesntHave('cotizaciones')->count();
        $postMortems = Proyecto::whereIn('estado', ['cerrado', 'cancelado', 'perdido'])->count();

        $pipelineMonto = $pipeline->sum(fn($p) => $p->cotizaciones->max('precio_venta_final') ?? 0);
        $adjudicadoMonto = $pipeline->whereIn('estado', ['adjudicado_pendiente', 'adjudicado_firmado', 'en_ejecucion', 'en_cierre'])->sum(fn($p) => $p->cotizaciones->max('precio_venta_final') ?? 0);

        $metaAdjudicacion = 12400000;
        $metaPct = $metaAdjudicacion > 0 ? round(($adjudicadoMonto / $metaAdjudicacion) * 100) : 0;

        return [
            'pipeline' => $pipeline,
            'pipelineTotal' => $pipelineTotal,
            'pipelineMonto' => $pipelineMonto,
            'adjudicadosCount' => $adjudicadosCount,
            'adjudicadoMonto' => $adjudicadoMonto,
            'hitRateConteo' => $hitRateConteo,
            'concentracionSedena' => $concentracionSedena,
            'porSublinea' => $porSublinea,
            'porEstado' => $porEstado,
            'porCliente' => $porCliente,
            'oportunidadesAtencion' => $oportunidadesAtencion,
            'cargaEquipo' => $cargaEquipo,
            'dossiersRiesgo' => $dossiersRiesgo,
            'postMortems' => $postMortems,
            'metaAdjudicacion' => $metaAdjudicacion,
            'metaPct' => $metaPct,
        ];
    }

    public function getStatusOfertasDataProperty(): array
    {
        // ── Datos reales: oportunidades + historial de ponderación por mes ──
        // Las columnas son los meses presentes en proyecto_ponderacion_historial.
        // Si solo hay un snapshot, se muestra esa única columna.
        $oportunidades = Proyecto::oportunidades()
            ->with([
                'cliente:id,alias,razon_social',
                'gerenteProyectos',
                'elaboro',
                'historialPonderacion.ponderacion',
            ])
            ->orderBy('cp_numero')
            ->get();

        // Construir lista cronológica de meses distintos (anio*100+mes) con etiquetas tipo "Ene 26"
        $mesesLabels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $monthKeys = $oportunidades
            ->flatMap(fn($o) => $o->historialPonderacion)
            ->map(fn($h) => $h->anio * 100 + $h->mes)
            ->unique()
            ->sort()
            ->values();

        // Fallback: si no hay historial todavía, una sola columna "Actual"
        if ($monthKeys->isEmpty()) {
            $months    = ['Actual'];
            $monthKeys = collect([null]);
        } else {
            $months = $monthKeys->map(function ($k) use ($mesesLabels) {
                $anio = intdiv($k, 100);
                $mes  = $k % 100;
                return $mesesLabels[$mes - 1] . ' ' . substr((string) $anio, -2);
            })->all();
        }
        $numMonths = count($months);

        $abreviaResponsable = function (?string $full): string {
            if (!$full) return 'Sin asignar';
            $parts = preg_split('/\s+/', trim($full));
            if (count($parts) === 1) return $parts[0];
            return $parts[0] . ' ' . mb_substr($parts[1], 0, 1);
        };

        $proyectos = [];
        foreach ($oportunidades as $o) {
            $pondActual = (int) ($o->ponderacion ?? 0);

            // Indexar el historial del proyecto por monthKey
            $historialByKey = $o->historialPonderacion->keyBy(fn($h) => $h->anio * 100 + $h->mes);

            // Construir el array probs[] siguiendo los meses globales
            $probs = [];
            foreach ($monthKeys as $k) {
                if ($k === null) {
                    $probs[] = $pondActual;
                } elseif ($historialByKey->has($k)) {
                    $probs[] = (int) $historialByKey[$k]->ponderacion?->porcentaje;
                } else {
                    $probs[] = null;
                }
            }

            $nombreCliente = $o->cliente?->alias ?? $o->cliente?->razon_social ?? 'Sin cliente';
            $empresa = $o->cliente?->razon_social ?? $o->cliente?->alias ?? 'Sin empresa';
            $nombreProyecto = trim($nombreCliente . ' ' . ($o->tech_reference ?? $o->cp_numero ?? '—'));

            $proyectos[] = [
                'nombre'      => $nombreProyecto,
                'empresa'     => $empresa,
                'tech_ref'    => $o->tech_reference ?? '—',
                'cp'          => $o->cp_numero ?? '—',
                'monto'       => round(((float) $o->monto_usd) / 1_000_000, 6),
                'probs'       => $probs,
                'resp'        => $abreviaResponsable($o->gerenteProyectos?->name ?? $o->elaboro?->name ?? null),
                'estado'      => $o->estado,
                'monto_usd'   => (float) $o->monto_usd,
                'plazo_meses' => (int) ($o->plazo_estimado ?? 0),
            ];
        }
        $montoByMonth      = array_fill(0, $numMonths, 0.0);
        $ponderadoByMonth   = array_fill(0, $numMonths, 0.0);
        $contratadoByMonth  = array_fill(0, $numMonths, 0.0);

        foreach ($proyectos as $p) {
            for ($i = 0; $i < $numMonths; $i++) {
                if ($p['probs'][$i] !== null && $p['probs'][$i] > 0) {
                    $montoByMonth[$i]     += $p['monto'];
                    $ponderadoByMonth[$i]  += $p['monto'] * $p['probs'][$i] / 100;
                    if ($p['probs'][$i] === 100) {
                        $contratadoByMonth[$i] += $p['monto'];
                    }
                }
            }
        }

        $montoByMonth      = array_map(fn($v) => round($v, 3), $montoByMonth);
        $ponderadoByMonth   = array_map(fn($v) => round($v, 3), $ponderadoByMonth);
        $contratadoByMonth  = array_map(fn($v) => round($v, 3), $contratadoByMonth);

        $palette = [
            ['b' => 'rgb(34,197,94)',   'bg' => 'rgba(34,197,94,0.10)'],
            ['b' => 'rgb(59,130,246)',   'bg' => 'rgba(59,130,246,0.10)'],
            ['b' => 'rgb(245,158,11)',   'bg' => 'rgba(245,158,11,0.10)'],
            ['b' => 'rgb(16,185,129)',   'bg' => 'rgba(16,185,129,0.10)'],
            ['b' => 'rgb(239,68,68)',    'bg' => 'rgba(239,68,68,0.10)'],
            ['b' => 'rgb(139,92,246)',   'bg' => 'rgba(139,92,246,0.10)'],
            ['b' => 'rgb(6,182,212)',    'bg' => 'rgba(6,182,212,0.10)'],
            ['b' => 'rgb(249,115,22)',   'bg' => 'rgba(249,115,22,0.10)'],
            ['b' => 'rgb(236,72,153)',   'bg' => 'rgba(236,72,153,0.10)'],
            ['b' => 'rgb(234,179,8)',    'bg' => 'rgba(234,179,8,0.10)'],
            ['b' => 'rgb(20,184,166)',   'bg' => 'rgba(20,184,166,0.10)'],
            ['b' => 'rgb(99,102,241)',   'bg' => 'rgba(99,102,241,0.10)'],
            ['b' => 'rgb(168,85,247)',   'bg' => 'rgba(168,85,247,0.10)'],
            ['b' => 'rgb(251,113,133)',  'bg' => 'rgba(251,113,133,0.10)'],
            ['b' => 'rgb(251,146,60)',   'bg' => 'rgba(251,146,60,0.10)'],
            ['b' => 'rgb(74,222,128)',   'bg' => 'rgba(74,222,128,0.10)'],
            ['b' => 'rgb(96,165,250)',   'bg' => 'rgba(96,165,250,0.10)'],
            ['b' => 'rgb(244,114,182)',  'bg' => 'rgba(244,114,182,0.10)'],
            ['b' => 'rgb(45,212,191)',   'bg' => 'rgba(45,212,191,0.10)'],
            ['b' => 'rgb(250,204,21)',   'bg' => 'rgba(250,204,21,0.10)'],
            ['b' => 'rgb(147,51,234)',   'bg' => 'rgba(147,51,234,0.10)'],
            ['b' => 'rgb(14,165,233)',   'bg' => 'rgba(14,165,233,0.10)'],
            ['b' => 'rgb(244,63,94)',    'bg' => 'rgba(244,63,94,0.10)'],
            ['b' => 'rgb(132,204,22)',   'bg' => 'rgba(132,204,22,0.10)'],
            ['b' => 'rgb(217,70,239)',   'bg' => 'rgba(217,70,239,0.10)'],
            ['b' => 'rgb(251,191,36)',   'bg' => 'rgba(251,191,36,0.10)'],
            ['b' => 'rgb(52,211,153)',   'bg' => 'rgba(52,211,153,0.10)'],
            ['b' => 'rgb(248,113,113)',  'bg' => 'rgba(248,113,113,0.10)'],
            ['b' => 'rgb(129,140,248)',  'bg' => 'rgba(129,140,248,0.10)'],
            ['b' => 'rgb(232,121,249)',  'bg' => 'rgba(232,121,249,0.10)'],
            ['b' => 'rgb(103,232,249)',  'bg' => 'rgba(103,232,249,0.10)'],
        ];

        foreach ($proyectos as $i => &$p) {
            $c = $palette[$i % count($palette)];
            $p['borderColor'] = $c['b'];
            $p['bgColor']     = $c['bg'];
        }
        unset($p);

        $bruto = 0;
        $esperado = 0;
        $count = 0;
        foreach ($proyectos as $item) {
            $bruto += $item['monto'];
            $lastProb = 0;
            for ($i = $numMonths - 1; $i >= 0; $i--) {
                if (($item['probs'][$i] ?? null) !== null) { $lastProb = $item['probs'][$i]; break; }
            }
            $esperado += $item['monto'] * $lastProb / 100;
            if ($item['monto'] > 0) $count++;
        }
        $eficiencia = $bruto > 0 ? round($esperado / $bruto * 100, 1) : 0;

        $carteraKpis = [
            'bruto'      => round($bruto, 2),
            'esperado'   => round($esperado, 2),
            'count'      => $count,
            'eficiencia' => $eficiencia,
        ];

        $thresholds = [0, 10, 25, 75, 100];
        $carteraLabels = ['0% Perdida', '10% Remoto', '25% Posible', '75% Probable', '100% Contratada'];
        $carteraArr = [];
        foreach ($thresholds as $tidx => $t) {
            $tBruto = 0;
            $tEsperado = 0;
            $tCount = 0;
            foreach ($proyectos as $p) {
                $lp = 0;
                for ($i = $numMonths - 1; $i >= 0; $i--) {
                    if (($p['probs'][$i] ?? null) !== null) { $lp = $p['probs'][$i]; break; }
                }
                if ($p['monto'] > 0 && $lp >= $t) {
                    $tBruto += $p['monto'];
                    $tEsperado += $p['monto'] * $lp / 100;
                    $tCount++;
                }
            }
            $carteraArr[] = ['label' => $carteraLabels[$tidx], 'threshold' => $t, 'bruto' => round($tBruto, 3), 'esperado' => round($tEsperado, 3), 'count' => $tCount];
        }
        $carteraData = [
            'labels'   => $carteraLabels,
            'bruto'    => array_column($carteraArr, 'bruto'),
            'esperado' => array_column($carteraArr, 'esperado'),
            'counts'   => array_column($carteraArr, 'count'),
        ];

        $bands = [
            ['key' => 'p100', 'min' => 100, 'max' => 100, 'label' => '100% Contratada'],
            ['key' => 'p75',  'min' => 75,  'max' => 99,  'label' => '75% Probable'],
            ['key' => 'p25',  'min' => 25,  'max' => 74,  'label' => '25% Posible'],
            ['key' => 'p10',  'min' => 10,  'max' => 24,  'label' => '10% Remoto'],
            ['key' => 'p0',   'min' => 0,   'max' => 0,   'label' => '0% Perdida'],
        ];

        $evolucion = [];
        foreach ($bands as $band) {
            $evolucion[$band['key']] = array_fill(0, $numMonths, 0.0);
        }
        $evoBruto     = array_fill(0, $numMonths, 0.0);
        $evoPonderado = array_fill(0, $numMonths, 0.0);

        foreach ($proyectos as $p) {
            for ($i = 0; $i < $numMonths; $i++) {
                $prob = $p['probs'][$i];
                if ($prob !== null && $prob > 0) {
                    $evoBruto[$i] += $p['monto'];
                    $evoPonderado[$i] += $p['monto'] * $prob / 100;
                    foreach ($bands as $band) {
                        if ($prob >= $band['min'] && $prob <= $band['max']) {
                            $evolucion[$band['key']][$i] += $p['monto'];
                            break;
                        }
                    }
                }
            }
        }

        $evoBruto     = array_map(fn($v) => round($v, 3), $evoBruto);
        $evoPonderado = array_map(fn($v) => round($v, 3), $evoPonderado);
        foreach ($bands as $band) {
            $evolucion[$band['key']] = array_map(fn($v) => round($v, 3), $evolucion[$band['key']]);
        }

        $levelResumen = [];
        foreach ($bands as $band) {
            $lBruto = 0;
            $lPond = 0;
            $lCount = 0;
            foreach ($proyectos as $p) {
                $lp = 0;
                for ($i = $numMonths - 1; $i >= 0; $i--) {
                    if ($p['probs'][$i] !== null) { $lp = $p['probs'][$i]; break; }
                }
                if ($lp >= $band['min'] && $lp <= $band['max'] && $p['monto'] > 0) {
                    $lBruto += $p['monto'];
                    $lPond += $p['monto'] * $lp / 100;
                    $lCount++;
                }
            }
            $levelResumen[] = [
                'key'  => $band['key'],
                'label' => $band['label'],
                'bruto' => round($lBruto, 2),
                'pond'  => round($lPond, 2),
                'count' => $lCount,
                'eficiencia' => $lBruto > 0 ? round($lPond / $lBruto * 100, 1) : 0,
            ];
        }

        return [
            'months'             => $months,
            'proyectos'          => $proyectos,
            'montoByMonth'       => $montoByMonth,
            'ponderadoByMonth'   => $ponderadoByMonth,
            'contratadoByMonth'  => $contratadoByMonth,
            'carteraKpis'        => $carteraKpis,
            'carteraData'        => $carteraData,
            'carteraEvolucion'  => [
                'months'      => $months,
                'bands'       => $bands,
                'series'      => $evolucion,
                'bruto'       => $evoBruto,
                'ponderado'   => $evoPonderado,
            ],
            'levelResumen'      => $levelResumen,
            'byResponsable'    => (function() use ($proyectos, $numMonths) {
                $grouped = [];
                foreach ($proyectos as $p) {
                    $r = $p['resp'] ?: 'Sin asignar';
                    if (!isset($grouped[$r])) { $grouped[$r] = ['bruto' => 0, 'pond' => 0, 'count' => 0]; }
                    $lp = 0;
                    for ($i = $numMonths - 1; $i >= 0; $i--) {
                        if (($p['probs'][$i] ?? null) !== null) { $lp = $p['probs'][$i]; break; }
                    }
                    $grouped[$r]['bruto'] += $p['monto'];
                    $grouped[$r]['pond']  += $p['monto'] * $lp / 100;
                    $grouped[$r]['count']++;
                }
                // Orden descendente por bruto, "Sin asignar" siempre al final
                uksort($grouped, function($a, $b) use ($grouped) {
                    if ($a === 'Sin asignar') return 1;
                    if ($b === 'Sin asignar') return -1;
                    return $grouped[$b]['bruto'] <=> $grouped[$a]['bruto'];
                });
                $labels = []; $brutos = []; $ponds = []; $counts = [];
                foreach ($grouped as $r => $g) {
                    $labels[] = $r;
                    $brutos[] = round($g['bruto'], 3);
                    $ponds[]  = round($g['pond'], 3);
                    $counts[] = $g['count'];
                }
                return ['labels' => $labels, 'bruto' => $brutos, 'pond' => $ponds, 'counts' => $counts];
            })(),
        ];
    }

    /**
     * KPI "Meses de trabajo sin contratación" (a.k.a. Backlog Months / Backlog Runway).
     *
     * Indicador clásico de carga contratada: cuánto trabajo quedaría por delante
     * — medido en meses — si la empresa no firmara un solo contrato nuevo a partir
     * de hoy. Se calcula como Cartera Contratada por Ejecutar / Producción Mensual.
     *
     *   meses_sin_contratacion = backlog_contratado / produccion_mensual_promedio
     *
     * - Backlog contratado: suma de monto_usd de oportunidades con ponderación = 100
     *   o cuyo estado ya esté en la cadena de adjudicación/ejecución.
     * - Producción mensual: derivada del propio backlog distribuyendo cada proyecto
     *   sobre su plazo_estimado (meses). Si el proyecto no tiene plazo, se usa el
     *   plazo promedio del resto; si nadie lo tiene, se asume 6 meses por defecto.
     */
    public function getMesesSinContratacionDataProperty(): array
    {
        $estadosContratados = ['adjudicado_pendiente', 'adjudicado_firmado', 'en_ejecucion', 'en_cierre'];

        $contratados = Proyecto::oportunidades()
            ->with('cliente:id,alias,razon_social')
            ->where(function ($q) use ($estadosContratados) {
                $q->where('ponderacion', '>=', 100)
                  ->orWhereIn('estado', $estadosContratados);
            })
            ->get();

        $plazoDefault = 6;
        $plazosConocidos = $contratados->pluck('plazo_estimado')->filter(fn($v) => (int) $v > 0);
        $plazoPromedio = $plazosConocidos->count() > 0
            ? (int) round($plazosConocidos->avg())
            : $plazoDefault;

        $backlogUsd = 0.0;
        $produccionMensualUsd = 0.0;
        $proyectosDetalle = [];

        foreach ($contratados as $p) {
            $monto = (float) ($p->monto_usd ?? 0);
            if ($monto <= 0) continue;

            $plazo = (int) $p->plazo_estimado;
            $plazoUsado = $plazo > 0 ? $plazo : $plazoPromedio;
            $produccionProyecto = $monto / max($plazoUsado, 1);

            $backlogUsd          += $monto;
            $produccionMensualUsd += $produccionProyecto;

            $proyectosDetalle[] = [
                'nombre'      => $p->cliente?->alias ?? $p->cliente?->razon_social ?? '—',
                'cp'          => $p->cp_numero ?? '—',
                'tech_ref'    => $p->tech_reference,
                'estado'      => $p->estado,
                'monto_usd'   => $monto,
                'monto_m'     => round($monto / 1_000_000, 3),
                'plazo'       => $plazoUsado,
                'plazo_real'  => $plazo > 0,
                'mensual_m'   => round($produccionProyecto / 1_000_000, 3),
            ];
        }

        // Ordena por aporte mensual desc (los que más consumen capacidad arriba)
        usort($proyectosDetalle, fn($a, $b) => $b['mensual_m'] <=> $a['mensual_m']);

        $mesesSinContratacion = $produccionMensualUsd > 0
            ? round($backlogUsd / $produccionMensualUsd, 1)
            : 0.0;

        // Burn-down: backlog restante después de cada mes a la producción actual
        $burnMonths = (int) max(1, ceil($mesesSinContratacion + 2));
        $burnLabels = [];
        $burnSerie  = [];
        $restante   = $backlogUsd;
        for ($i = 0; $i <= $burnMonths; $i++) {
            $burnLabels[] = $i === 0 ? 'Hoy' : ('Mes ' . $i);
            $burnSerie[]  = round(max(0, $restante) / 1_000_000, 3);
            $restante     -= $produccionMensualUsd;
        }

        // Clasificación de la salud del runway (rangos típicos para industria de proyectos)
        $semaforo = match (true) {
            $mesesSinContratacion >= 9 => ['color' => 'emerald', 'label' => 'Saludable',   'desc' => 'Backlog cubre más de 9 meses de operación.'],
            $mesesSinContratacion >= 6 => ['color' => 'cyan',    'label' => 'Estable',     'desc' => 'Backlog entre 6 y 9 meses: sostenible a corto plazo.'],
            $mesesSinContratacion >= 3 => ['color' => 'amber',   'label' => 'En atención', 'desc' => 'Backlog 3-6 meses: priorizar nuevas adjudicaciones.'],
            $mesesSinContratacion >  0 => ['color' => 'red',     'label' => 'Crítico',     'desc' => 'Menos de 3 meses de carga contratada.'],
            default                    => ['color' => 'slate',   'label' => 'Sin datos',   'desc' => 'No hay backlog contratado registrado.'],
        };

        return [
            'meses'                  => $mesesSinContratacion,
            'backlog_usd'            => round($backlogUsd, 2),
            'backlog_m'              => round($backlogUsd / 1_000_000, 3),
            'produccion_mensual_usd' => round($produccionMensualUsd, 2),
            'produccion_mensual_m'   => round($produccionMensualUsd / 1_000_000, 3),
            'count_contratados'      => count($proyectosDetalle),
            'plazo_promedio'         => $plazoPromedio,
            'tiene_plazo_real'       => $plazosConocidos->count() > 0,
            'proyectos'              => $proyectosDetalle,
            'burn_labels'            => $burnLabels,
            'burn_serie'             => $burnSerie,
            'semaforo'               => $semaforo,
        ];
    }

    /** Chart data for stacked-bar and horizontal bars using real ponderacion */
    public function getChartDataProperty(): array
    {
        $pipeline = $this->dashboardData['pipeline'];

        $monthDefs = [
            ['year' => 2025, 'num' => 11, 'label' => 'Nov 25'],
            ['year' => 2025, 'num' => 12, 'label' => 'Dic 25'],
            ['year' => 2026, 'num' => 1,  'label' => 'Ene 26'],
            ['year' => 2026, 'num' => 2,  'label' => 'Feb 26'],
            ['year' => 2026, 'num' => 3,  'label' => 'Mar 26'],
            ['year' => 2026, 'num' => 4,  'label' => 'Abr 26'],
            ['year' => 2026, 'num' => 5,  'label' => 'May 26'],
            ['year' => 2026, 'num' => 6,  'label' => 'Jun 26'],
            ['year' => 2026, 'num' => 7,  'label' => 'Jul 26'],
            ['year' => 2026, 'num' => 8,  'label' => 'Ago 26'],
            ['year' => 2026, 'num' => 9,  'label' => 'Sep 26'],
            ['year' => 2026, 'num' => 10, 'label' => 'Oct 26'],
            ['year' => 2026, 'num' => 11, 'label' => 'Nov 26'],
            ['year' => 2026, 'num' => 12, 'label' => 'Dic 26'],
        ];

        $byMonth = [
            'p100' => array_fill(0, 14, 0),
            'p75'  => array_fill(0, 14, 0),
            'p25'  => array_fill(0, 14, 0),
            'p10'  => array_fill(0, 14, 0),
        ];

        foreach ($pipeline as $p) {
            $cot   = $p->cotizaciones->sortByDesc('version')->first();
            $monto = (float) ($cot?->precio_venta_final ?? 0);
            $fecha = $cot?->fecha_emision;
            $mes   = $fecha ? (int) $fecha->format('n') : null;
            $year  = $fecha ? (int) $fecha->format('Y') : null;
            $pond  = (int) ($p->ponderacion ?? 10);

            $colIdx = 2; // fallback: Ene 26
            foreach ($monthDefs as $idx => $m) {
                if ($mes == $m['num'] && $year == $m['year']) { $colIdx = $idx; break; }
            }
            $key = 'p' . $pond;
            if (isset($byMonth[$key])) {
                $byMonth[$key][$colIdx] = round($byMonth[$key][$colIdx] + ($monto / 1_000_000), 3);
            }
        }

        $bySublinea = $pipeline
            ->groupBy(fn ($p) => $p->sublinea?->codigo ?? 'N/A')
            ->map(fn ($g) => round($g->sum(fn ($p) => (float) ($p->cotizaciones->sortByDesc('version')->first()?->precio_venta_final ?? 0)) / 1_000_000, 3))
            ->sortDesc()->take(10)->toArray();

        $byCliente = $pipeline
            ->groupBy(fn ($p) => $p->cliente?->alias ?? 'N/A')
            ->map(fn ($g) => round($g->sum(fn ($p) => (float) ($p->cotizaciones->sortByDesc('version')->first()?->precio_venta_final ?? 0)) / 1_000_000, 3))
            ->sortDesc()->take(8)->toArray();

        return [
            'monthLabels' => array_column($monthDefs, 'label'),
            'byMonth'     => $byMonth,
            'bySublinea'  => $bySublinea,
            'byCliente'   => $byCliente,
        ];
    }

    public function render()
    {
        $data = $this->dashboardData;

        return view('livewire.ejecutivo.dashboard-index', [
            'pipeline' => $data['pipeline'],
            'pipelineTotal' => $data['pipelineTotal'],
            'pipelineMonto' => $data['pipelineMonto'],
            'adjudicadosCount' => $data['adjudicadosCount'],
            'adjudicadoMonto' => $data['adjudicadoMonto'],
            'hitRateConteo' => $data['hitRateConteo'],
            'concentracionSedena' => $data['concentracionSedena'],
            'porSublinea' => $data['porSublinea'],
            'porEstado' => $data['porEstado'],
            'porCliente' => $data['porCliente'],
            'oportunidadesAtencion' => $data['oportunidadesAtencion'],
            'cargaEquipo' => $data['cargaEquipo'],
            'dossiersRiesgo' => $data['dossiersRiesgo'],
            'postMortems' => $data['postMortems'],
            'metaAdjudicacion' => $data['metaAdjudicacion'],
            'metaPct' => $data['metaPct'],
            'clientes' => $this->clientes,
            'sublineas' => $this->sublineas,
            'nombre_usuario' => auth()->user()->name ?? 'Usuario',
            'quarter_label' => 'Q' . ceil(now()->month / 3) . ' ' . now()->year,
            'chartData' => $this->chartData,
            'statusOfertasData' => $this->statusOfertasData,
            'carteraKpis' => $this->statusOfertasData['carteraKpis'],
            'carteraData' => $this->statusOfertasData['carteraData'],
            'carteraEvolucion' => $this->statusOfertasData['carteraEvolucion'],
            'levelResumen' => $this->statusOfertasData['levelResumen'],
            'byResponsable' => $this->statusOfertasData['byResponsable'],
            'mesesSinContratacion' => $this->mesesSinContratacionData,
        ])->layout('components.layouts.app');
    }
}