<?php

namespace App\Livewire\Proyectos;

use App\Models\Proyectos\Proyecto;
use Livewire\Component;
use Livewire\WithPagination;

class SuministrosIndex extends Component
{
    use WithPagination;

    public $proyectoId = '';

    protected $queryString = ['proyectoId' => ['except' => '']];

    public function updatingProyectoId()
    {
        $this->resetPage();
    }

    public function getProyectosProperty()
    {
        return Proyecto::whereIn('estado', ['en_ejecucion', 'en_cierre'])
            ->with(['cliente', 'sublinea'])
            ->orderBy('cp_numero')
            ->get();
    }

    public function render()
    {
        $selectedProyecto = null;
        $listado = null;

        if ($this->proyectoId) {
            $selectedProyecto = Proyecto::find($this->proyectoId);
            if ($selectedProyecto) {
                $listado = $selectedProyecto->bomBoeItems()->orderBy('tipo')->orderBy('descripcion')->paginate(25, ['*'], 'items');
            }
        }

        return view('livewire.proyectos.suministros-index', [
            'selectedProyecto' => $selectedProyecto,
            'listado' => $listado,
        ])->layout('components.layouts.app');
    }
}