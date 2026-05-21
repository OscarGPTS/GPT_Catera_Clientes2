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
        $query = Proyecto::with(['cliente', 'sublinea', 'lugar', 'gerenteProyectos', 'elaboro', 'cotizaciones']);

        if ($this->search) {
            $search = strtolower($this->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(cp_numero) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(dn_numero) LIKE ?', ["%{$search}%"])
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
            $query->whereHas('miembros', fn($q) => $q->where('user_id', auth()->id())
                ->where('rol', 'gerente_proyectos'));
        }

        return $query->orderByRaw('fecha_envio IS NULL, fecha_envio DESC')->paginate($this->perPage);
    }

    public function getKpisProperty()
    {
        $baseQuery = Proyecto::whereNotIn('estado', ['cancelado', 'perdido', 'archivado']);

        if ($this->anio && $this->anio !== 'todos') {
            $baseQuery->where('anio', (int) $this->anio);
        }

        $pipeline = (clone $baseQuery)->count();
        $pipelineMonto = (float) (clone $baseQuery)->sum('monto_usd');
        $montoPonderado = (float) (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(monto_usd * ponderacion / 100), 0) as ponderado')
            ->value('ponderado');
        $ponderacionMedia = $pipeline > 0
            ? (int) round((clone $baseQuery)->avg('ponderacion') ?? 0)
            : 0;

        $adjudicadoQuery = (clone $baseQuery)
            ->whereIn('estado', ['adjudicado_pendiente', 'adjudicado_firmado', 'en_ejecucion']);
        $adjudicadoMonto = (float) (clone $adjudicadoQuery)->sum('monto_usd');
        $adjudicadoCount = (clone $adjudicadoQuery)->count();

        $enviadasCount = (clone $baseQuery)
            ->whereNotIn('estado', ['en_revision', 'cotizando'])
            ->count();

        return [
            'pipeline_monto' => $pipelineMonto,
            'pipeline_count' => $pipeline,
            'monto_ponderado' => $montoPonderado,
            'ponderacion_media' => $ponderacionMedia,
            'adjudicado_monto' => $adjudicadoMonto,
            'adjudicado_count' => $adjudicadoCount,
            'enviadas_count' => $enviadasCount,
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