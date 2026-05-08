<?php

namespace App\Livewire\Proyectos;

use App\Services\Asignaciones\SnapshotService;
use Livewire\Component;

class AsignacionesIndex extends Component
{
    public $anio;
    public $trimestre = 1;
    public $gerencia = '';
    public $drawerOpen = false;
    public $drawerPersonaIdx = null;

    public $successMessage = '';
    public $errorMessage = '';

    public function mount()
    {
        $this->anio = now()->year;
        $this->trimestre = ceil(now()->month / 3);
    }

    public function updatedAnio()
    {
        $this->resetPageState();
    }

    public function updatedTrimestre()
    {
        $this->resetPageState();
    }

    public function updatedGerencia()
    {
        $this->resetPageState();
    }

    public function generarSnapshot(SnapshotService $service)
    {
        $this->authorize('admin');

        $mes = $this->trimestreToMes((int) $this->trimestre);

        try {
            $service->generar($mes, (int) $this->anio);
            $this->successMessage = 'Snapshot generado correctamente para Q' . $this->trimestre . ' ' . $this->anio . '.';
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al generar el snapshot: ' . $e->getMessage();
        }
    }

    public function openDrawer($personaIdx)
    {
        $this->drawerPersonaIdx = $personaIdx;
        $this->drawerOpen = true;
    }

    public function closeDrawer()
    {
        $this->drawerOpen = false;
        $this->drawerPersonaIdx = null;
    }

    public function getSnapshotDataProperty()
    {
        $mes = $this->trimestreToMes((int) $this->trimestre);
        $service = new SnapshotService();
        $data = $service->getSnapshotData($mes, (int) $this->anio);

        if ($this->gerencia) {
            $data['asignaciones'] = collect($data['asignaciones'])
                ->filter(fn($a) => ($a['gerencia'] ?? '') === $this->gerencia)
                ->values()
                ->all();
        }

        return $data;
    }

    public function getDrawerPersonaProperty()
    {
        if ($this->drawerPersonaIdx === null) {
            return null;
        }

        $data = $this->snapshotData;
        $asignaciones = $data['asignaciones'] ?? [];

        return $asignaciones[$this->drawerPersonaIdx] ?? null;
    }

    public function render()
    {
        $data = $this->snapshotData;
        $anios = range(now()->year, now()->year - 4);

        return view('livewire.proyectos.asignaciones-index', [
            'asignaciones' => $data['asignaciones'] ?? [],
            'equipoTotal' => $data['equipo_total'] ?? 0,
            'proyectosActivos' => $data['proyectos_activos'] ?? 0,
            'cargaPromedio' => $data['carga_promedio'] ?? '0',
            'sobrecarga' => $data['sobrecarga'] ?? 0,
            'snapshotGenerado' => $data['snapshot_generado'] ?? false,
            'anios' => $anios,
            'mes' => $this->trimestreToMes((int) $this->trimestre),
        ])->layout('components.layouts.app');
    }

    protected function trimestreToMes(int $trimestre): int
    {
        return match ($trimestre) {
            1 => 1,
            2 => 4,
            3 => 7,
            4 => 10,
            default => 1,
        };
    }

    protected function resetPageState()
    {
        $this->successMessage = '';
        $this->errorMessage = '';
    }
}