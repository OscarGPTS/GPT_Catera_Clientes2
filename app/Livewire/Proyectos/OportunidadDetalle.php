<?php

namespace App\Livewire\Proyectos;

use App\Models\Proyectos\Proyecto;
use App\Models\User;
use Livewire\Component;

class OportunidadDetalle extends Component
{
    public Proyecto $proyecto;
    public $activeTab = 'info';
    public $showAprobarModal = false;
    public $showRechazarModal = false;
    public $showEquipoModal = false;
    public $showEstadoModal = false;

    public $gerente_proyectos_id = '';
    public $notas_aprobar = '';
    public $notas_rechazar = '';
    public $cambiar_estado = '';
    public $cambiar_notas = '';

    public $director_dn_id = '';
    public $gerente_proyectos_id_equipo = '';
    public $gerente_operaciones_id = '';
    public $ingeniero_costos_id = '';
    public $ingeniero_proyectos_id = '';
    public $trainee_id = '';

    public function mount(Proyecto $proyecto)
    {
        $this->proyecto = $proyecto;
        $this->cambiar_estado = $proyecto->estado;
        $this->fillTeamFromProyecto();
    }

    protected function fillTeamFromProyecto()
    {
        $this->director_dn_id = $this->proyecto->director_dn_id ?? '';
        $this->gerente_proyectos_id_equipo = $this->proyecto->gerente_proyectos_id ?? '';
        $this->gerente_operaciones_id = $this->proyecto->gerente_operaciones_id ?? '';
        $this->ingeniero_costos_id = $this->proyecto->ingeniero_costos_id ?? '';
        $this->ingeniero_proyectos_id = $this->proyecto->ingeniero_proyectos_id ?? '';
        $this->trainee_id = $this->proyecto->trainee_id ?? '';
    }

    public function aprobarCp()
    {
        $this->authorize('aprobar cp');

        $this->validate([
            'gerente_proyectos_id' => 'required|exists:users,id',
            'notas_aprobar' => 'nullable|string|max:5000',
        ]);

        $this->proyecto->update([
            'estado' => 'cotizando',
            'gerente_proyectos_id' => $this->gerente_proyectos_id,
            'notas' => ($this->proyecto->notas ? $this->proyecto->notas . "\n" : '') . 'CP aprobado: ' . ($this->notas_aprobar ?? ''),
        ]);

        $this->proyecto->eventos()->create([
            'tipo' => 'cp_aprobado',
            'user_id' => auth()->id(),
            'comentario' => 'CP aprobado por Comité Comercial. Gerente asignado: ' . User::find($this->gerente_proyectos_id)->name,
        ]);

        $this->showAprobarModal = false;
        $this->gerente_proyectos_id = '';
        $this->notas_aprobar = '';
        session()->flash('success', 'CP aprobado correctamente.');
    }

    public function rechazarCp()
    {
        $this->authorize('aprobar cp');

        $this->validate([
            'notas_rechazar' => 'required|string|max:5000',
        ]);

        $this->proyecto->update([
            'estado' => 'perdido',
            'notas' => ($this->proyecto->notas ? $this->proyecto->notas . "\n" : '') . 'CP rechazado: ' . $this->notas_rechazar,
        ]);

        $this->proyecto->eventos()->create([
            'tipo' => 'cp_rechazado',
            'user_id' => auth()->id(),
            'comentario' => 'CP rechazado: ' . $this->notas_rechazar,
        ]);

        $this->showRechazarModal = false;
        $this->notas_rechazar = '';
        session()->flash('success', 'CP rechazado.');
    }

    public function asignarEquipo()
    {
        $this->authorize('asignar cp');

        $this->validate([
            'director_dn_id' => 'nullable|exists:users,id',
            'gerente_proyectos_id_equipo' => 'nullable|exists:users,id',
            'gerente_operaciones_id' => 'nullable|exists:users,id',
            'ingeniero_costos_id' => 'nullable|exists:users,id',
            'ingeniero_proyectos_id' => 'nullable|exists:users,id',
            'trainee_id' => 'nullable|exists:users,id',
        ]);

        $this->proyecto->update([
            'director_dn_id' => $this->director_dn_id ?: null,
            'gerente_proyectos_id' => $this->gerente_proyectos_id_equipo ?: null,
            'gerente_operaciones_id' => $this->gerente_operaciones_id ?: null,
            'ingeniero_costos_id' => $this->ingeniero_costos_id ?: null,
            'ingeniero_proyectos_id' => $this->ingeniero_proyectos_id ?: null,
            'trainee_id' => $this->trainee_id ?: null,
        ]);

        $this->proyecto->eventos()->create([
            'tipo' => 'equipo_asignado',
            'user_id' => auth()->id(),
            'comentario' => 'Equipo asignado al CP.',
        ]);

        $this->showEquipoModal = false;
        $this->proyecto->refresh();
        $this->fillTeamFromProyecto();
        session()->flash('success', 'Equipo asignado correctamente.');
    }

    public function getEquipoDisponibleProperty()
    {
        return User::role(['gerente_proyectos', 'ingeniero_proyectos', 'ingeniero_costos', 'trainee_proyectos', 'gerente_operaciones'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function render()
    {
        $this->proyecto->load([
            'cliente.contactos',
            'sublinea',
            'directorDn',
            'gerenteProyectos',
            'gerenteOperaciones',
            'ingenieroCostos',
            'ingenieroProyectos',
            'trainee',
            'eventos.user',
            'cotizaciones.partidas',
            'solicitudesInterna.items',
        ]);

        return view('livewire.proyectos.oportunidad-detalle', [
            'equipoDisponible' => $this->equipoDisponible,
        ])->layout('components.layouts.app');
    }
}