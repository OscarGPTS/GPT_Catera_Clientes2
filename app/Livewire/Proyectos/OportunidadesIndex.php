<?php

namespace App\Livewire\Proyectos;

use App\Models\Comercial\Cliente;
use App\Models\Comercial\Sublinea;
use App\Models\Proyectos\Proyecto;
use Livewire\Component;
use Livewire\WithPagination;

class OportunidadesIndex extends Component
{
    use WithPagination;

    public $anio = '';
    public $search = '';
    public $sublineasSeleccionadas = [];
    public $estadosSeleccionados = [];
    public $soloMios = false;
    public $perPage = 25;

    public function mount()
    {
        $this->anio = request()->query('anio', (string) date('Y'));
        $this->search = request()->query('search', '');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingAnio()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->sublineasSeleccionadas = [];
        $this->estadosSeleccionados = [];
        $this->soloMios = false;
        $this->resetPage();
    }

    public function getSublineasProperty()
    {
        return Sublinea::orderBy('codigo')->get();
    }

    public function getEstadosProperty()
    {
        return [
            'en_revision' => 'En revisión',
            'cotizando' => 'Cotizando',
            'cotizado' => 'Cotizado',
            'presentado' => 'Presentado',
            'adjudicado_pendiente' => 'Adjudicado pend.',
            'adjudicado_firmado' => 'Adjudicado',
            'en_ejecucion' => 'En ejecución',
            'en_cierre' => 'En cierre',
            'cerrado' => 'Cerrado',
            'cancelado' => 'Cancelado',
            'perdido' => 'Perdido',
            'archivado' => 'Archivado',
        ];
    }

    public function getOportunidadesProperty()
    {
        $query = Proyecto::with(['cliente', 'sublinea', 'gerenteProyectos', 'cotizaciones']);

        if ($this->search) {
            $search = strtolower($this->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(cp_numero) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(tech_reference) LIKE ?', ["%{$search}%"])
                    ->orWhereHas('cliente', fn($c) => $c->whereRaw('LOWER(razon_social) LIKE ?', ["%{$search}%"]))
                    ->orWhereRaw('LOWER(usuario_final) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($this->anio && $this->anio !== 'todos') {
            $query->where('anio', (int) $this->anio);
        }

        if (!empty($this->sublineasSeleccionadas)) {
            $query->whereIn('sublinea_id', $this->sublineasSeleccionadas);
        }

        if (!empty($this->estadosSeleccionados)) {
            $query->whereIn('estado', $this->estadosSeleccionados);
        }

        if ($this->soloMios) {
            $query->where('gerente_proyectos_id', auth()->id());
        }

        return $query->latest()->paginate($this->perPage);
    }

    public function getKpisProperty()
    {
        $baseQuery = Proyecto::whereNotIn('estado', ['cancelado', 'perdido', 'archivado']);

        if ($this->anio && $this->anio !== 'todos') {
            $baseQuery->where('anio', (int) $this->anio);
        }

        $pipeline = (clone $baseQuery)->count();
        $pipelineMonto = (clone $baseQuery)
            ->withSum('cotizaciones as monto_total', 'precio_venta_final')
            ->get()
            ->sum('monto_total');

        $adjudicados = (clone $baseQuery)
            ->whereIn('estado', ['adjudicado_pendiente', 'adjudicado_firmado', 'en_ejecucion'])
            ->count();
        $adjudicadoMonto = (clone $baseQuery)
            ->whereIn('estado', ['adjudicado_pendiente', 'adjudicado_firmado', 'en_ejecucion'])
            ->withSum('cotizaciones as monto_total', 'precio_venta_final')
            ->get()
            ->sum('monto_total');

        $total = max(Proyecto::count(), 1);
        $adjudicadosCount = Proyecto::whereIn('estado', ['adjudicado_pendiente', 'adjudicado_firmado', 'en_ejecucion', 'cerrado'])->count();
        $hitRateConteo = round(($adjudicadosCount / $total) * 100);

        $sedenaMonto = (clone $baseQuery)
            ->whereHas('cliente', fn($q) => $q->whereRaw('LOWER(razon_social) LIKE ?', ['%sedena%']))
            ->withSum('cotizaciones as monto_total', 'precio_venta_final')
            ->get()
            ->sum('monto_total');

        $sedenaConcentracion = $pipelineMonto > 0 ? round(($sedenaMonto / $pipelineMonto) * 100, 1) : 0;

        return [
            'pipeline_monto' => $pipelineMonto,
            'pipeline_count' => $pipeline,
            'adjudicado_monto' => $adjudicadoMonto,
            'adjudicado_count' => $adjudicados,
            'hit_rate_conteo' => $hitRateConteo,
            'hit_rate_monto' => $pipelineMonto > 0 ? round(($adjudicadoMonto / $pipelineMonto) * 100) : 0,
            'sedena_concentracion' => $sedenaConcentracion,
        ];
    }

    public function render()
    {
        return view('livewire.proyectos.oportunidades-index', [
            'oportunidades' => $this->oportunidades,
            'sublineas' => $this->sublineas,
            'estados' => $this->estados,
            'kpis' => $this->kpis,
        ])->layout('components.layouts.app');
    }
}