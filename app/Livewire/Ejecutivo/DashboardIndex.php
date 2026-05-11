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
        ])->layout('components.layouts.app');
    }
}