<?php

namespace App\Livewire\Proyectos;

use App\Models\Comercial\Cliente;
use App\Models\Comercial\Sublinea;
use App\Models\Proyectos\Proyecto;
use Livewire\Component;
use Livewire\WithPagination;

class ProyectosIndex extends Component
{
    use WithPagination;

    public $buscar = '';
    public $estado = '';
    public $clienteId = '';
    public $sublineaId = '';

    protected $queryString = [
        'buscar' => ['except' => ''],
        'estado' => ['except' => ''],
        'clienteId' => ['except' => ''],
        'sublineaId' => ['except' => ''],
    ];

    public function updatingBuscar()
    {
        $this->resetPage();
    }

    public function updatingEstado()
    {
        $this->resetPage();
    }

    public function updatingClienteId()
    {
        $this->resetPage();
    }

    public function updatingSublineaId()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['buscar', 'estado', 'clienteId', 'sublineaId']);
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

    public function getEstadosProperty()
    {
        return collect([
            ['value' => 'adjudicado_firmado', 'label' => 'Adjudicado'],
            ['value' => 'en_ejecucion', 'label' => 'En ejecución'],
            ['value' => 'en_cierre', 'label' => 'En cierre'],
            ['value' => 'cerrado', 'label' => 'Cerrado'],
            ['value' => 'cancelado', 'label' => 'Cancelado'],
        ]);
    }

    public function render()
    {
        $query = Proyecto::with(['cliente', 'sublinea', 'gerenteProyectos', 'cotizaciones', 'libroProyecto'])
            ->whereIn('estado', ['adjudicado_firmado', 'en_ejecucion', 'en_cierre']);

        if ($this->estado) {
            $query->where('estado', $this->estado);
        }

        if ($this->buscar) {
            $buscar = strtolower($this->buscar);
            $query->where(function ($q) use ($buscar) {
                $q->whereRaw('LOWER(cp_numero) LIKE ?', ["%{$buscar}%"])
                    ->orWhereRaw('LOWER(dn_numero) LIKE ?', ["%{$buscar}%"])
                    ->orWhereRaw('LOWER(tech_reference) LIKE ?', ["%{$buscar}%"])
                    ->orWhereHas('cliente', fn($c) => $c->whereRaw('LOWER(razon_social) LIKE ?', ["%{$buscar}%"]));
            });
        }

        if ($this->clienteId) {
            $query->where('cliente_id', $this->clienteId);
        }

        if ($this->sublineaId) {
            $query->where('sublinea_id', $this->sublineaId);
        }

        $proyectos = $query->latest()->paginate(20)->withQueryString();

        $estadoLabels = [
            'adjudicado_firmado' => 'Adjudicado',
            'en_ejecucion' => 'En ejecución',
            'en_cierre' => 'En cierre',
            'cerrado' => 'Cerrado',
            'cancelado' => 'Cancelado',
        ];

        return view('livewire.proyectos.proyectos-index', [
            'proyectos' => $proyectos,
            'estadoLabels' => $estadoLabels,
        ])->layout('components.layouts.app');
    }
}