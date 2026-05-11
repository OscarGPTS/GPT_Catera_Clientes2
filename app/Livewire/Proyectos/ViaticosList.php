<?php

namespace App\Livewire\Proyectos;

use App\Models\Proyectos\Proyecto;
use App\Models\Proyectos\SolicitudViatico;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class ViaticosList extends Component
{
    use WithPagination;

    public $tab = 'pendientes';
    public $filterProyectoId = '';
    public $newModalOpen = false;
    public $newStep = 1;
    public $detailViaticoId = null;
    public $showRechazoTextarea = false;
    public $rechazoMotivo = '';

    public $proyectoId = '';
    public $fechaInicio = '';
    public $fechaFin = '';
    public $justificacion = '';
    public $lugar = '';
    public $personalSeleccionado = [];
    public $partidas = [];

    public $successMessage = '';
    public $errorMessage = '';

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->proyectoId = '';
        $this->fechaInicio = '';
        $this->fechaFin = '';
        $this->justificacion = '';
        $this->lugar = '';
        $this->personalSeleccionado = [];
        $this->partidas = [
            ['concepto' => 'Hospedaje', 'dias' => 0, 'monto_estimado' => 0],
            ['concepto' => 'Alimentos', 'dias' => 0, 'monto_estimado' => 0],
            ['concepto' => 'Transporte aéreo', 'dias' => 0, 'monto_estimado' => 0],
            ['concepto' => 'Transporte terrestre', 'dias' => 0, 'monto_estimado' => 0],
            ['concepto' => 'Transporte local', 'dias' => 0, 'monto_estimado' => 0],
            ['concepto' => 'Otros', 'dias' => 0, 'monto_estimado' => 0],
        ];
        $this->newStep = 1;
    }

    public function openNewModal()
    {
        $this->resetForm();
        $this->newModalOpen = true;
    }

    public function closeNewModal()
    {
        $this->newModalOpen = false;
    }

    public function nextStep()
    {
        if ($this->newStep < 4) {
            $this->newStep++;
        }
    }

    public function prevStep()
    {
        if ($this->newStep > 1) {
            $this->newStep--;
        }
    }

    protected $listeners = ['add-person' => 'addPersonalFromEvent'];

    public function addPersonalFromEvent($userId, $userName)
    {
        $dias = $this->calcDiasPeriodo();
        if (!collect($this->personalSeleccionado)->contains('user_id', $userId)) {
            $this->personalSeleccionado[] = [
                'user_id' => $userId,
                'name' => $userName,
                'dias' => max(1, $dias),
                'categoria' => 'Técnico',
                'anticipo' => 0,
            ];
        }
    }

    public function removePersonal($index)
    {
        unset($this->personalSeleccionado[$index]);
        $this->personalSeleccionado = array_values($this->personalSeleccionado);
    }

    public function calcDiasPeriodo()
    {
        if (!$this->fechaInicio || !$this->fechaFin) {
            return 0;
        }
        $start = \Carbon\Carbon::parse($this->fechaInicio);
        $end = \Carbon\Carbon::parse($this->fechaFin);
        $diff = $start->diffInDays($end) + 1;
        return max(0, $diff);
    }

    public function getTotalMontoProperty()
    {
        return collect($this->partidas)->sum(fn($p) => (float)($p['monto_estimado'] ?? 0));
    }

    public function submitSolicitud()
    {
        $this->validate([
            'proyectoId' => 'required|exists:proyectos,id',
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio',
            'justificacion' => 'required|string|max:2000',
            'lugar' => 'required|string|max:255',
        ], [
            'proyectoId.required' => 'Selecciona un proyecto.',
            'fechaInicio.required' => 'Indica la fecha de inicio.',
            'fechaFin.required' => 'Indica la fecha de fin.',
            'justificacion.required' => 'Describe la justificación del viaje.',
            'lugar.required' => 'Indica el destino del viaje.',
        ]);

        $total = $this->totalMonto;

        $solicitud = SolicitudViatico::create([
            'proyecto_id' => $this->proyectoId,
            'fecha_inicio' => $this->fechaInicio,
            'fecha_fin' => $this->fechaFin,
            'destino' => $this->lugar,
            'motivo' => $this->justificacion,
            'solicitante_id' => auth()->id(),
            'status' => 'pendiente_serv_grales',
            'monto_total' => $total,
        ]);

        $this->successMessage = 'Solicitud de viáticos creada correctamente.';
        $this->closeNewModal();
    }

    public function openDetail($viaticoId)
    {
        $this->detailViaticoId = $viaticoId;
        $this->showRechazoTextarea = false;
        $this->rechazoMotivo = '';
    }

    public function closeDetail()
    {
        $this->detailViaticoId = null;
    }

    public function aprobar($viaticoId)
    {
        $viatico = SolicitudViatico::findOrFail($viaticoId);

        if ($viatico->status === 'pendiente_serv_grales') {
            $this->authorize('aprobar viaticos servicios generales');
            $viatico->update([
                'status' => 'pendiente_direccion',
                'aprobado_por_id' => auth()->id(),
                'aprobado_at' => now(),
            ]);
            $this->successMessage = 'Viáticos aprobados por Servicios Generales.';
        } elseif ($viatico->status === 'pendiente_direccion') {
            $this->authorize('aprobar viaticos direccion');
            $viatico->update([
                'status' => 'aprobado',
                'aprobado_por_id' => auth()->id(),
                'aprobado_at' => now(),
            ]);
            $this->successMessage = 'Viáticos aprobados por Dirección.';
        }

        $this->detailViaticoId = null;
    }

    public function rechazar($viaticoId)
    {
        $this->validate([
            'rechazoMotivo' => 'required|string|max:500',
        ], [
            'rechazoMotivo.required' => 'Indica el motivo del rechazo.',
        ]);

        $viatico = SolicitudViatico::findOrFail($viaticoId);
        $viatico->update([
            'status' => 'rechazado',
            'motivo_rechazo' => $this->rechazoMotivo,
        ]);

        $this->successMessage = 'Solicitud rechazada.';
        $this->detailViaticoId = null;
        $this->rechazoMotivo = '';
        $this->showRechazoTextarea = false;
    }

    public function getSolicitudesProperty()
    {
        $query = SolicitudViatico::with(['proyecto.cliente', 'solicitante', 'aprobadoPor']);

        match ($this->tab) {
            'pendientes' => $query->whereIn('status', ['pendiente_serv_grales', 'pendiente_direccion', 'borrador']),
            'aprobadas' => $query->where('status', 'aprobado'),
            'rechazadas' => $query->where('status', 'rechazado'),
            default => null,
        };

        if ($this->filterProyectoId) {
            $query->where('proyecto_id', $this->filterProyectoId);
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    public function getPendientesCountProperty()
    {
        return SolicitudViatico::whereIn('status', ['pendiente_serv_grales', 'pendiente_direccion', 'borrador'])->count();
    }

    public function getDetailViaticoProperty()
    {
        if (!$this->detailViaticoId) {
            return null;
        }
        return SolicitudViatico::with(['proyecto.cliente', 'solicitante', 'aprobadoPor'])
            ->find($this->detailViaticoId);
    }

    public function render()
    {
        return view('livewire.proyectos.viaticos-list', [
            'proyectos' => Proyecto::whereIn('estado', ['en_ejecucion', 'en_cierre', 'adjudicado_firmado'])
                ->with('cliente')
                ->orderBy('cp_numero')
                ->get(),
            'personalDisponible' => User::where('status', 'active')->orderBy('name')->get(),
        ])->layout('components.layouts.app');
    }
}