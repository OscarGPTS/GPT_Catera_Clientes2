<?php

namespace App\Livewire\Proyectos;

use App\Models\Proyectos\MinutaEntrega;
use App\Models\Proyectos\MinutaEntregaParticipante;
use App\Models\Proyectos\Proyecto;
use App\Settings\SystemSettings;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MinutaForm extends Component
{
    public Proyecto $proyecto;
    public $minutaObligatoria = true;
    public $currentStep = 0;

    public $fecha_reunion = '';
    public $hora_inicio = '';
    public $hora_fin = '';
    public $modalidad = 'presencial';
    public $ordenDia = [['descripcion' => '']];
    public $acuerdos = [['descripcion' => '', 'responsable' => '', 'fechaCompromiso' => '']];
    public $participantesSeleccionados = [];
    public $rolesParticipantes = [];

    public $steps = [
        ['label' => 'Datos básicos', 'status' => 'current'],
        ['label' => 'Orden del día', 'status' => 'pending'],
        ['label' => 'Acuerdos', 'status' => 'pending'],
        ['label' => 'Participantes', 'status' => 'pending'],
        ['label' => 'Preview & firma', 'status' => 'pending'],
    ];

    public $successMessage = '';

    protected $rules = [
        'fecha_reunion' => 'required|date',
        'hora_inicio' => 'required',
        'hora_fin' => 'required|after:hora_inicio',
        'modalidad' => 'required|in:presencial,virtual,mixta',
        'ordenDia' => 'required|array|min:1',
        'ordenDia.*.descripcion' => 'nullable|string|max:500',
        'acuerdos' => 'nullable|array',
        'acuerdos.*.descripcion' => 'nullable|string|max:1000',
        'acuerdos.*.responsable' => 'nullable|string',
        'acuerdos.*.fechaCompromiso' => 'nullable|date',
        'participantesSeleccionados' => 'required|array|min:2',
    ];

    protected $messages = [
        'fecha_reunion.required' => 'La fecha de reunión es obligatoria.',
        'hora_inicio.required' => 'La hora de inicio es obligatoria.',
        'hora_fin.required' => 'La hora de fin es obligatoria.',
        'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
        'modalidad.required' => 'La modalidad es obligatoria.',
        'participantesSeleccionados.required' => 'Debe seleccionar al menos 2 participantes.',
        'participantesSeleccionados.min' => 'Debe seleccionar al menos 2 participantes.',
    ];

    public function mount(Proyecto $proyecto)
    {
        $this->proyecto = $proyecto;
        $this->minutaObligatoria = app(SystemSettings::class)->minuta_entrega_obligatoria;

        if ($proyecto->minutaEntrega) {
            $minuta = $proyecto->minutaEntrega;
            $this->fecha_reunion = $minuta->fecha_reunion?->format('Y-m-d') ?? '';
            $this->hora_inicio = $minuta->hora_inicio?->format('H:i') ?? '';
            $this->hora_fin = $minuta->hora_fin?->format('H:i') ?? '';
            $this->modalidad = $minuta->modalidad ?? 'presencial';
            $this->ordenDia = $minuta->orden_del_dia
                ? collect($minuta->orden_del_dia)->map(fn($d) => ['descripcion' => $d])->toArray()
                : [['descripcion' => '']];
            $this->acuerdos = $minuta->acuerdos
                ? collect($minuta->acuerdos)->map(fn($a) => [
                    'descripcion' => $a['descripcion'] ?? $a ?? '',
                    'responsable' => $a['responsable'] ?? '',
                    'fechaCompromiso' => $a['fechaCompromiso'] ?? $a['fecha_compromiso'] ?? '',
                ])->toArray()
                : [['descripcion' => '', 'responsable' => '', 'fechaCompromiso' => '']];

            $this->participantesSeleccionados = $minuta->participantes->pluck('user_id')->toArray();
            foreach ($minuta->participantes as $p) {
                $this->rolesParticipantes[$p->user_id] = $p->rol_en_minuta;
            }
        }
    }

    public function nextStep()
    {
        if ($this->currentStep === 0) {
            $this->validate([
                'fecha_reunion' => 'required|date',
                'hora_inicio' => 'required',
                'hora_fin' => 'required|after:hora_inicio',
                'modalidad' => 'required|in:presencial,virtual,mixta',
            ]);
        }

        if ($this->currentStep < count($this->steps) - 1) {
            $this->currentStep++;
            $this->updateSteps();
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 0) {
            $this->currentStep--;
            $this->updateSteps();
        }
    }

    public function updateSteps()
    {
        for ($i = 0; $i < count($this->steps); $i++) {
            if ($i < $this->currentStep) {
                $this->steps[$i]['status'] = 'completed';
            } elseif ($i === $this->currentStep) {
                $this->steps[$i]['status'] = 'current';
            } else {
                $this->steps[$i]['status'] = 'pending';
            }
        }
    }

    public function agregarPunto()
    {
        $this->ordenDia[] = ['descripcion' => ''];
    }

    public function eliminarPunto($index)
    {
        if (count($this->ordenDia) > 1) {
            unset($this->ordenDia[$index]);
            $this->ordenDia = array_values($this->ordenDia);
        }
    }

    public function agregarAcuerdo()
    {
        $this->acuerdos[] = ['descripcion' => '', 'responsable' => '', 'fechaCompromiso' => ''];
    }

    public function eliminarAcuerdo($index)
    {
        if (count($this->acuerdos) > 1) {
            unset($this->acuerdos[$index]);
            $this->acuerdos = array_values($this->acuerdos);
        }
    }

    public function guardarBorrador()
    {
        if ($this->proyecto->minutaEntrega) {
            $this->proyecto->minutaEntrega->update([
                'fecha_reunion' => $this->fecha_reunion,
                'hora_inicio' => $this->hora_inicio,
                'hora_fin' => $this->hora_fin,
                'modalidad' => $this->modalidad,
                'orden_del_dia' => collect($this->ordenDia)->pluck('descripcion')->filter()->values()->toArray(),
                'acuerdos' => collect($this->acuerdos)->filter(fn($a) => !empty($a['descripcion']))->values()->toArray(),
                'status' => 'borrador',
            ]);
        } else {
            $this->validate([
                'fecha_reunion' => 'required|date',
                'hora_inicio' => 'required',
                'hora_fin' => 'required|after:hora_inicio',
                'modalidad' => 'required|in:presencial,virtual,mixta',
            ]);

            $minuta = MinutaEntrega::create([
                'proyecto_id' => $this->proyecto->id,
                'fecha_reunion' => $this->fecha_reunion,
                'hora_inicio' => $this->hora_inicio,
                'hora_fin' => $this->hora_fin,
                'modalidad' => $this->modalidad,
                'orden_del_dia' => collect($this->ordenDia)->pluck('descripcion')->filter()->values()->toArray(),
                'acuerdos' => collect($this->acuerdos)->filter(fn($a) => !empty($a['descripcion']))->values()->toArray(),
                'status' => 'borrador',
            ]);

            foreach ($this->participantesSeleccionados as $userId) {
                MinutaEntregaParticipante::create([
                    'minuta_id' => $minuta->id,
                    'user_id' => $userId,
                    'rol_en_minuta' => $this->rolesParticipantes[$userId] ?? 'asistente',
                ]);
            }

            $this->proyecto->eventos()->create([
                'tipo' => 'minuta_creada',
                'user_id' => Auth::id(),
                'comentario' => 'Minuta de entrega creada (borrador).',
            ]);
        }

        $this->successMessage = 'Minuta guardada como borrador.';
    }

    public function firmarYemitir()
    {
        $this->validate([
            'fecha_reunion' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required|after:hora_inicio',
            'modalidad' => 'required|in:presencial,virtual,mixta',
            'participantesSeleccionados' => 'required|array|min:2',
        ]);

        $this->authorize('firmar minuta');

        if ($this->proyecto->minutaEntrega) {
            $minuta = $this->proyecto->minutaEntrega;
            $minuta->update([
                'fecha_reunion' => $this->fecha_reunion,
                'hora_inicio' => $this->hora_inicio,
                'hora_fin' => $this->hora_fin,
                'modalidad' => $this->modalidad,
                'orden_del_dia' => collect($this->ordenDia)->pluck('descripcion')->filter()->values()->toArray(),
                'acuerdos' => collect($this->acuerdos)->filter(fn($a) => !empty($a['descripcion']))->values()->toArray(),
                'status' => 'firmada',
                'firmado_at' => now(),
            ]);
        } else {
            $minuta = MinutaEntrega::create([
                'proyecto_id' => $this->proyecto->id,
                'fecha_reunion' => $this->fecha_reunion,
                'hora_inicio' => $this->hora_inicio,
                'hora_fin' => $this->hora_fin,
                'modalidad' => $this->modalidad,
                'orden_del_dia' => collect($this->ordenDia)->pluck('descripcion')->filter()->values()->toArray(),
                'acuerdos' => collect($this->acuerdos)->filter(fn($a) => !empty($a['descripcion']))->values()->toArray(),
                'status' => 'firmada',
                'firmado_at' => now(),
            ]);

            foreach ($this->participantesSeleccionados as $userId) {
                MinutaEntregaParticipante::create([
                    'minuta_id' => $minuta->id,
                    'user_id' => $userId,
                    'rol_en_minuta' => $this->rolesParticipantes[$userId] ?? 'asistente',
                ]);
            }
        }

        $this->proyecto->eventos()->create([
            'tipo' => 'minuta_firmada',
            'user_id' => Auth::id(),
            'comentario' => 'Minuta de entrega firmada.',
        ]);

        $this->successMessage = 'Minuta firmada y emitida correctamente.';
    }

    public function getUsuariosProperty()
    {
        return \App\Models\User::where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function getUsuariosMapProperty()
    {
        return $this->usuarios->mapWithKeys(fn($u) => [$u->id => $u->name])->toArray();
    }

    public function getExistingMinutaProperty()
    {
        return $this->proyecto->minutaEntrega;
    }

    public function render()
    {
        return view('livewire.proyectos.minuta-form', [
            'usuarios' => $this->usuarios,
        ])->layout('components.layouts.app');
    }
}