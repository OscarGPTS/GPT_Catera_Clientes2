<?php

namespace App\Livewire\Proyectos;

use App\Models\Ponderacion;
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
    public $showPonderacionModal = false;

    public $gerente_proyectos_id = '';
    public $notas_aprobar = '';
    public $notas_rechazar = '';
    public $cambiar_estado = '';
    public $cambiar_notas = '';

    // ── Modal: actualizar ponderación por mes ───────────────────────────────
    public ?int $pond_ponderacion_id = null;
    public ?int $pond_anio           = null;
    public ?int $pond_mes            = null;
    public ?string $pond_notas       = null;

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

        // Default modal de ponderación: mes/año actual + ponderación vigente
        $this->pond_anio = (int) now()->year;
        $this->pond_mes  = (int) now()->month;
        $this->pond_ponderacion_id = Ponderacion::porPorcentaje((int) $proyecto->ponderacion)?->id;
    }

    public function abrirPonderacionModal(): void
    {
        // Si ya hay un snapshot para el mes actual, precarga su valor
        $this->pond_anio = (int) now()->year;
        $this->pond_mes  = (int) now()->month;
        $snap = $this->proyecto->historialPonderacion()
            ->where('anio', $this->pond_anio)
            ->where('mes', $this->pond_mes)
            ->orderByDesc('id')
            ->first();
        $this->pond_ponderacion_id = $snap?->ponderacion_id
            ?? Ponderacion::porPorcentaje((int) $this->proyecto->ponderacion)?->id;
        $this->pond_notas = null;
        $this->showPonderacionModal = true;
    }

    public function guardarPonderacion(): void
    {
        $this->validate([
            'pond_ponderacion_id' => 'required|integer|exists:ponderaciones,id',
            'pond_anio'           => 'required|integer|min:2020|max:2099',
            'pond_mes'            => 'required|integer|min:1|max:12',
            'pond_notas'          => 'nullable|string|max:500',
        ]);

        $this->proyecto->registrarPonderacion(
            (int) $this->pond_ponderacion_id,
            (int) $this->pond_anio,
            (int) $this->pond_mes,
            $this->pond_notas ?: null
        );

        $this->proyecto->refresh();
        $this->showPonderacionModal = false;
        $this->pond_notas = null;

        session()->flash('success', 'Ponderación registrada correctamente.');
    }

    protected function fillTeamFromProyecto()
    {
        $this->director_dn_id              = $this->proyecto->getMiembroIdPorRol('director_dn') ?? '';
        $this->gerente_proyectos_id_equipo = $this->proyecto->getMiembroIdPorRol('gerente_proyectos') ?? '';
        $this->gerente_operaciones_id      = $this->proyecto->getMiembroIdPorRol('gerente_operaciones') ?? '';
        $this->ingeniero_costos_id         = $this->proyecto->getMiembroIdPorRol('ingeniero_costos') ?? '';
        $this->ingeniero_proyectos_id      = $this->proyecto->getMiembroIdPorRol('ingeniero_proyectos') ?? '';
        $this->trainee_id                  = $this->proyecto->getMiembroIdPorRol('trainee') ?? '';
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
            'notas' => ($this->proyecto->notas ? $this->proyecto->notas . "\n" : '') . 'CP aprobado: ' . ($this->notas_aprobar ?? ''),
        ]);

        $this->proyecto->setMiembroPorRol((int) $this->gerente_proyectos_id, 'gerente_proyectos');

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

        $this->proyecto->update([/* no hay campos de equipo directos */]);

        $this->proyecto->setMiembroPorRol($this->director_dn_id ? (int)$this->director_dn_id : null, 'director_dn');
        $this->proyecto->setMiembroPorRol($this->gerente_proyectos_id_equipo ? (int)$this->gerente_proyectos_id_equipo : null, 'gerente_proyectos');
        $this->proyecto->setMiembroPorRol($this->gerente_operaciones_id ? (int)$this->gerente_operaciones_id : null, 'gerente_operaciones');
        $this->proyecto->setMiembroPorRol($this->ingeniero_costos_id ? (int)$this->ingeniero_costos_id : null, 'ingeniero_costos');
        $this->proyecto->setMiembroPorRol($this->ingeniero_proyectos_id ? (int)$this->ingeniero_proyectos_id : null, 'ingeniero_proyectos');
        $this->proyecto->setMiembroPorRol($this->trainee_id ? (int)$this->trainee_id : null, 'trainee');

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
            'historialPonderacion.ponderacion',
            'historialPonderacion.user',
        ]);

        return view('livewire.proyectos.oportunidad-detalle', [
            'equipoDisponible' => $this->equipoDisponible,
            'ponderacionesCatalogo' => Ponderacion::active()->orderBy('orden')->orderBy('porcentaje')->get(),
        ])->layout('components.layouts.app');
    }
}