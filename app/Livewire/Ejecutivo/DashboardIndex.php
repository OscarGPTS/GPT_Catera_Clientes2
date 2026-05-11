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
        ])->layout('components.layouts.app');
    }
}