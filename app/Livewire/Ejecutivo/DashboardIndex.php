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
            ->with(['cliente', 'sublinea', 'gerenteProyectos', 'cotizaciones'])
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

        $gerentes = $pipeline->whereNotNull('gerente_proyectos_id')->groupBy('gerente_proyectos_id');
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
        $months = ['Nov', 'Dic', 'Ene', 'Feb', 'Mar', 'Abr', 'May'];

        $proyectos = [
            ['nombre' => 'IGASAMEX HTS 30x4" Oleofinos',             'cp' => '152/25',    'monto' => 0.037,  'probs' => [75,  75,  75,  75,  75,  75,  75],  'resp' => 'Fernando B'],
            ['nombre' => 'ENGIE HTP 42x24" VDR',                     'cp' => '157/25',    'monto' => 0.933,  'probs' => [null, null, null, 25,  25,  25,  25],  'resp' => 'Kevin P'],
            ['nombre' => 'PIR SYSTEM HT 30x20" Cactus',              'cp' => '001/26',    'monto' => 0.103,  'probs' => [null, null, 10,  10,  10,  10,  10],  'resp' => 'Fernando B'],
            ['nombre' => 'NATURGY Anillos separadores',               'cp' => '002/26',    'monto' => 0.064,  'probs' => [null, null, 25,  75, 100, 100, 100],  'resp' => 'Fernando B'],
            ['nombre' => 'IGASAMEX VCP Dif. Diámetros',               'cp' => '003/26',    'monto' => 0.029,  'probs' => [null, null, 10,  10,  10,  10,  10],  'resp' => 'Fernando B'],
            ['nombre' => 'PROTEXA Válvulas Cluster SEJKAN',           'cp' => '004/26',    'monto' => 1.721,  'probs' => [null, null, null, 10,  10,  10,  10],  'resp' => 'Fernando B'],
            ['nombre' => 'EUROINOVA DLS 6" 600#',                    'cp' => '005/26',    'monto' => 0.121,  'probs' => [null, null, 10,  10,  10,  10,  10],  'resp' => 'Fernando B'],
            ['nombre' => 'MOLPER DLS 6" 600# Hidalgo',                'cp' => '016/26',    'monto' => 2.977,  'probs' => [null, null, 10,  25,  25,  25,  25],  'resp' => 'Aquiles G'],
            ['nombre' => 'SERPORT HTP 24x16" Submarino',              'cp' => '006/26',    'monto' => 0.352,  'probs' => [null, null, 10,  10,  10,  10,  10],  'resp' => 'Sergio O'],
            ['nombre' => 'GCI HT 8x8" Nafta',                        'cp' => '007/26',    'monto' => 0.009,  'probs' => [null, null, 25,  25,  25,  25,  25],  'resp' => 'Diego R'],
            ['nombre' => 'ICA HTSF 24x24" Naucalpan',                 'cp' => '008/26',    'monto' => 1.602,  'probs' => [null, null, 25,  10,  10,  10,  10],  'resp' => 'Diego R'],
            ['nombre' => 'SICIM HT 30x20 600# Ags',                  'cp' => '009/26',    'monto' => 0.256,  'probs' => [null, null, null, 10,  10,  10,  10],  'resp' => 'Fernando B'],
            ['nombre' => 'COPC Juntas dieléctricas',                  'cp' => '010/26',    'monto' => 0.005,  'probs' => [null, null, 10,  10,  10,  10,  10],  'resp' => 'Fernando B'],
            ['nombre' => 'INDHECA Separador Horiz. Bakte',            'cp' => 'N/A',       'monto' => 0.376,  'probs' => [null, null, null, 25,  25,  25,  25],  'resp' => 'Guadalupe O'],
            ['nombre' => 'SARREAL Drillings 2" Niple',                'cp' => 'N/A',       'monto' => 0.045,  'probs' => [null, null, null, 25,  25,  25,  25],  'resp' => 'Fernando B'],
            ['nombre' => 'ARSEAL Válvulas Trunnion 8y10',             'cp' => '011/26',    'monto' => 0.069,  'probs' => [null, null, null, 10,  10,  10,  10],  'resp' => 'Fernando B'],
            ['nombre' => 'ESENTIA DLSS 36" Villa de Reyes',           'cp' => '012/26',    'monto' => 1.260,  'probs' => [null, null, null, 25,  25,  25,  25],  'resp' => 'Fernando B'],
            ['nombre' => 'ESENTIA HTP 8" y 2" Samalayuca',            'cp' => '013/26',    'monto' => 0.063,  'probs' => [null, null, null, 25,  25,  25,  25],  'resp' => 'Fernando B'],
            ['nombre' => 'SEDENA Frente 10 Tren Mx-Qro',             'cp' => '-',         'monto' => 9.403,  'probs' => [null, null, null, 25,  75,  75,  75],  'resp' => 'Aquiles G'],
            ['nombre' => 'SEDENA Frente 11 Tren Mx-Qro',             'cp' => '-',         'monto' => 15.079, 'probs' => [null, null, null, 25,  75,  75,  75],  'resp' => 'Aquiles G'],
            ['nombre' => 'TC ENERGY VBT Dos Bocas',                   'cp' => '150/25',    'monto' => 0.060,  'probs' => [10,  10,  10,  25,  25,  25,  25],  'resp' => 'Fernando B'],
            ['nombre' => 'IGASAMEX Revisión empate 6"',               'cp' => '014/26',    'monto' => 0.001,  'probs' => [null, null, null, 75,  75,  75,  75],  'resp' => 'Fernando B'],
            ['nombre' => 'IGASAMEX PH VBT 4" Atlacomulco',            'cp' => '016/26',    'monto' => 0.007,  'probs' => [null, null, null, null, 100, 100, 100], 'resp' => 'Fernando B'],
            ['nombre' => 'ENGIE HTS 16x6" Meprosa',                  'cp' => '-',         'monto' => 0.353,  'probs' => [null, null, null, 25,  25,  25,  25],  'resp' => 'Fernando B'],
            ['nombre' => 'GRUPO 3VTA Válvula 24" 600#',              'cp' => '-',         'monto' => 0.223,  'probs' => [null, null, null, null, 10,  10,  10],  'resp' => 'Fernando B'],
            ['nombre' => 'INDHECA Tubería AC 24"',                   'cp' => '-',         'monto' => 1.274,  'probs' => [null, null, null, null, 25,  25,  25],  'resp' => 'Guadalupe O'],
            ['nombre' => 'GEOLIS HTP 16x12 600# RF',                 'cp' => '-',         'monto' => 0.147,  'probs' => [null, null, null, null, 75,  75,  75],  'resp' => 'Guadalupe O'],
            ['nombre' => 'SARREAL Válvulas 4,8,20 900#',             'cp' => '-',         'monto' => 0.524,  'probs' => [null, null, null, null, 75,  75,  75],  'resp' => 'Guadalupe O'],
            ['nombre' => 'PIFUSA Juntas Aislantes',                   'cp' => '022/26',    'monto' => 0.024,  'probs' => [null, null, null, null, null, 25,  25],  'resp' => 'Fernando B'],
            ['nombre' => 'COCOMEX HTP 8x8 Veracruz',                 'cp' => 'CP-021/26', 'monto' => 0.070,  'probs' => [null, null, null, null, null, 25,  25],  'resp' => 'Sergio O'],
            ['nombre' => 'MARABIS VBT 14 y 8',                       'cp' => 'CP-027/26', 'monto' => 0.031,  'probs' => [null, null, null, null, null, null, 25], 'resp' => 'Sergio O'],
        ];

        $numMonths = 7;
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
            for ($i = 6; $i >= 0; $i--) {
                if ($item['probs'][$i] !== null) { $lastProb = $item['probs'][$i]; break; }
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
                for ($i = 6; $i >= 0; $i--) {
                    if ($p['probs'][$i] !== null) { $lp = $p['probs'][$i]; break; }
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
                $order = ['Aquiles G', 'Fernando B', 'Guadalupe O', 'Diego R', 'Kevin P', 'Sergio O'];
                $grouped = [];
                foreach ($order as $r) { $grouped[$r] = ['bruto' => 0, 'pond' => 0, 'count' => 0]; }
                foreach ($proyectos as $p) {
                    $r = $p['resp'];
                    if (!isset($grouped[$r])) { $grouped[$r] = ['bruto' => 0, 'pond' => 0, 'count' => 0]; }
                    $lp = 0;
                    for ($i = $numMonths - 1; $i >= 0; $i--) {
                        if ($p['probs'][$i] !== null) { $lp = $p['probs'][$i]; break; }
                    }
                    $grouped[$r]['bruto'] += $p['monto'];
                    $grouped[$r]['pond'] += $p['monto'] * $lp / 100;
                    $grouped[$r]['count']++;
                }
                $labels = []; $brutos = []; $ponds = []; $counts = [];
                foreach ($order as $r) {
                    $labels[] = $r;
                    $brutos[] = round($grouped[$r]['bruto'], 3);
                    $ponds[] = round($grouped[$r]['pond'], 3);
                    $counts[] = $grouped[$r]['count'];
                }
                return ['labels' => $labels, 'bruto' => $brutos, 'pond' => $ponds, 'counts' => $counts];
            })(),
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
            ->groupBy(fn ($p) => $p->cliente?->alias_3letras ?? 'N/A')
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
        ])->layout('components.layouts.app');
    }
}