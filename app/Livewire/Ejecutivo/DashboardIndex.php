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
        $months = [
            'Nov 25', 'Dic 25', 'Ene 26', 'Feb 26', 'Mar 26', 'Abr 26',
            'May 26', 'Jun 26', 'Jul 26', 'Ago 26', 'Sep 26', 'Oct 26',
            'Nov 26', 'Dic 26',
        ];

        $proyectos = [
            ['nombre' => 'IGASAMEX HTS 30x4" Oleofinos',           'cp' => '152/25', 'monto' => 0.037,  'probs' => [50,  75,  75,  75,  75,  75, 100, 100, 100, 100, 100, 100, 100, 100]],
            ['nombre' => 'ENGIE HTP 42x24" VDR',                  'cp' => '157/25', 'monto' => 0.933,  'probs' => [null,null, 10,  25,  25,  50,  50,  75,  75,  75, 100, 100, 100, 100]],
            ['nombre' => 'PIR SYSTEM HT 30x20" Cactus',            'cp' => '1',      'monto' => 0.103,  'probs' => [null,null, 10,  10,  10,  25,  25,  25,  25,   0,   0,   0,   0,   0]],
            ['nombre' => 'NATURGY Anillos separadores',            'cp' => '2',      'monto' => 0.064,  'probs' => [null,null, 25,  50,  50,  75, 100, 100, 100, 100, 100, 100, 100, 100]],
            ['nombre' => 'IGASAMEX VCP Dif. Diámetros',            'cp' => '3',      'monto' => 0.029,  'probs' => [null,null, 10,  10,  10,  10,  25,  25,  25,  25,  50,  50,  50,  50]],
            ['nombre' => 'PROTEXA Válvulas Cluster SEJKAN',       'cp' => '4',      'monto' => 1.721,  'probs' => [null,null, 10,  10,  10,  25,  25,  25,  50,  50,  50,  50,  75,  75]],
            ['nombre' => 'EUROINOVA DLS 6" 600#',                 'cp' => '5',      'monto' => 0.121,  'probs' => [null,null, 10,  10,  10,  10,  10,  25,  25,  25,  25,  50,  50,  50]],
            ['nombre' => 'MOLPER DLS 6" 600# Hidalgo',             'cp' => '16',     'monto' => 2.977,  'probs' => [null,null, 10,  25,  25,  25,  50,  50,  50,  75,  75, 100, 100, 100]],
            ['nombre' => 'SERPORT HTP 24x16" Submarino',           'cp' => '6',      'monto' => 0.352,  'probs' => [null,null, 10,  10,  10,  25,  25,  25,  25,  50,  50,  50,  50,  50]],
            ['nombre' => 'GCI HT 8x8" Nafta',                     'cp' => '7',      'monto' => 0.009,  'probs' => [null,null, 10,  25,  25,  50,  50,  75,  75, 100, 100, 100, 100, 100]],
            ['nombre' => 'ICA HTSF 24x24" Naucalpan',              'cp' => '8',      'monto' => 1.045,  'probs' => [null,null, 10,  25,  25,  50,  75,  75, 100, 100, 100, 100, 100, 100]],
            ['nombre' => 'SICIM HT 30x20 600# Ags',               'cp' => '9',      'monto' => 0.256,  'probs' => [null,null, 10,  10,  10,  25,  25,  25,  50,  50,  50,  75,  75,  75]],
            ['nombre' => 'COPC Juntas dieléctricas',               'cp' => '10',     'monto' => 0.005,  'probs' => [null,null, 10,  10,  10,  25,  50,  50,  50,  75, 100, 100, 100, 100]],
            ['nombre' => 'INDHECA Separador Horiz. Bakte',         'cp' => '-',      'monto' => 0.376,  'probs' => [null,null, 10,  25,  25,  50,  50,  50,  75,  75, 100, 100, 100, 100]],
            ['nombre' => 'SARREAL Drillings 2" Niple',             'cp' => '-',      'monto' => 0.045,  'probs' => [null,null, 10,  25,  25,  50,  50,  50,  50,  75, 100, 100, 100, 100]],
            ['nombre' => 'ARSEAL Válvulas Trunnion 8y10',          'cp' => '11',     'monto' => 0.069,  'probs' => [null,null, 10,  10,  10,  10,  25,  25,  25,  25,  50,  50,  50,  50]],
            ['nombre' => 'ESENTIA DLSS 36" Villa de Reyes',       'cp' => '12',     'monto' => 1.260,  'probs' => [null,null, 10,  25,  25,  50,  50,  75,  75,  75, 100, 100, 100, 100]],
            ['nombre' => 'ESENTIA HTP 8" y 2" Samalayuca',         'cp' => '13',     'monto' => 0.063,  'probs' => [null,null, 10,  25,  25,  25,  50,  50,  50,  75,  75,  75, 100, 100]],
            ['nombre' => 'SEDENA Frente 10 Tren Mx-Qro',          'cp' => '14',     'monto' => 8.892,  'probs' => [null,null, 10,  25,  25,  50,  50,  75,  75, 100, 100, 100, 100, 100]],
            ['nombre' => 'SEDENA Frente 11 Tren Mx-Qro',          'cp' => '15',     'monto' => 36.375, 'probs' => [null,null, 10,  25,  25,  25,  50,  50,  75,  75, 100, 100, 100, 100]],
        ];

        $montoByMonth      = array_fill(0, 14, 0.0);
        $ponderadoByMonth   = array_fill(0, 14, 0.0);
        $contratadoByMonth  = array_fill(0, 14, 0.0);

        foreach ($proyectos as $p) {
            for ($i = 0; $i < 14; $i++) {
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
        ];

        foreach ($proyectos as $i => &$p) {
            $c = $palette[$i % count($palette)];
            $p['borderColor'] = $c['b'];
            $p['bgColor']     = $c['bg'];
        }
        unset($p);

        return [
            'months'             => $months,
            'proyectos'          => $proyectos,
            'montoByMonth'       => $montoByMonth,
            'ponderadoByMonth'   => $ponderadoByMonth,
            'contratadoByMonth'  => $contratadoByMonth,
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
            'p50'  => array_fill(0, 14, 0),
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
        ])->layout('components.layouts.app');
    }
}