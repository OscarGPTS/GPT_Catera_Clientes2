<?php

namespace App\Livewire\Proyectos;

use App\Models\Proyectos\BitacoraDiaria;
use App\Models\Proyectos\Proyecto;
use Livewire\Component;

class BitacoraForm extends Component
{
    public $vista = 'nueva';
    public $paso = 0;
    public $proyectoId = '';
    public $fecha = '';
    public $confirmado = false;

    public $personal = [];
    public $subcontratistas = [];
    public $equipos = [];
    public $actividades = '';
    public $desviacion = [
        'activa' => false,
        'tipo' => '',
        'severidad' => '',
        'descripcion' => '',
    ];
    public $vobo = [
        'nombre' => '',
        'organizacion' => '',
        'fecha' => '',
    ];

    public $proyectos = [];
    public $personalAsignado = [];
    public $equiposBoe = [];
    public $bitacoras = [];
    public $successMessage = '';
    public $errorMessage = '';

    public function mount()
    {
        $this->fecha = now()->format('Y-m-d');
        $this->vobo['fecha'] = now()->format('Y-m-d');
        $this->proyectos = Proyecto::whereIn('estado', ['en_ejecucion', 'en_cierre'])
            ->with(['cliente', 'sublinea'])
            ->orderBy('cp_numero')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'cp' => $p->cp_numero,
                'nombre' => $p->nombre ?? ($p->cliente?->razon_social ?? ''),
            ])
            ->values()
            ->toArray();
    }

    public function updatedProyectoId()
    {
        if (!$this->proyectoId) {
            $this->personalAsignado = [];
            $this->equiposBoe = [];
            $this->personal = [];
            $this->equipos = [];
            $this->bitacoras = [];
            return;
        }

        $proyecto = Proyecto::with(['asignaciones.user', 'bomBoeItems'])->find($this->proyectoId);

        if (!$proyecto) {
            return;
        }

        $this->personalAsignado = $proyecto->asignaciones
            ->map(fn($a) => [
                'id' => $a->user_id,
                'nombre' => $a->user?->name ?? '—',
                'puesto' => $a->user?->puesto ?? $a->rol ?? '—',
            ])
            ->values()
            ->toArray();

        $this->personal = collect($this->personalAsignado)->map(fn($p) => [
            'id' => $p['id'],
            'nombre' => $p['nombre'],
            'puesto' => $p['puesto'],
            'presente' => true,
            'horas' => 8,
            'extra' => false,
        ])->values()->toArray();

        $this->equiposBoe = $proyecto->bomBoeItems
            ->filter(fn($item) => in_array($item->tipo, ['equipo', 'boe']))
            ->map(fn($item) => [
                'id' => $item->id,
                'nombre' => $item->descripcion,
                'tipo' => $item->tipo,
            ])
            ->values()
            ->toArray();

        $this->equipos = collect($this->equiposBoe)->map(fn($e) => [
            'id' => $e['id'],
            'nombre' => $e['nombre'],
            'tipo' => $e['tipo'],
            'enSitio' => true,
            'horometro' => '',
            'estado' => 'operativo',
        ])->values()->toArray();

        $this->loadBitacoras();
    }

    public function loadBitacoras()
    {
        if (!$this->proyectoId) {
            $this->bitacoras = [];
            return;
        }

        $this->bitacoras = BitacoraDiaria::where('proyecto_id', $this->proyectoId)
            ->with('cargadoPor')
            ->orderBy('fecha', 'desc')
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'numero' => $b->id,
                'fecha' => $b->fecha->format('Y-m-d'),
                'estado' => $b->firmado_at ? 'firmada' : 'borrador',
                'actividades' => $b->relacion_actividades,
                'responsable' => $b->cargadoPor?->name ?? '—',
                'tiene_desviacion' => !empty($b->personal_gpt) && !empty(array_filter($b->personal_gpt, fn($p) => ($p['extra'] ?? false))),
            ])
            ->values()
            ->toArray();
    }

    public function irPaso($n)
    {
        $this->paso = $n;
    }

    public function agregarSubcontratista()
    {
        $this->subcontratistas[] = ['nombre' => '', 'empresa' => '', 'horas' => 8];
    }

    public function removerSubcontratista($index)
    {
        unset($this->subcontratistas[$index]);
        $this->subcontratistas = array_values($this->subcontratistas);
    }

    public function insertarShortcut($texto)
    {
        $this->actividades = $this->actividades ? $this->actividades . "\n" . $texto . ' ' : $texto . ' ';
    }

    public function getTotalHorasProperty()
    {
        $horasPersonal = collect($this->personal)->filter(fn($p) => $p['presente'])->sum(fn($p) => (int)($p['horas'] ?? 0));
        $horasSub = collect($this->subcontratistas)->sum(fn($s) => (int)($s['horas'] ?? 0));
        return $horasPersonal + $horasSub;
    }

    public function getTotalPresentesProperty()
    {
        return collect($this->personal)->filter(fn($p) => $p['presente'])->count() + count($this->subcontratistas);
    }

    public function getTotalEquiposSitioProperty()
    {
        return collect($this->equipos)->filter(fn($e) => $e['enSitio'])->count();
    }

    public function guardarBorrador()
    {
        $this->saveBitacora(false);
    }

    public function firmarEnviar()
    {
        if (!$this->confirmado) {
            return;
        }
        $this->saveBitacora(true);
    }

    protected function saveBitacora(bool $firmar)
    {
        $this->validate([
            'proyectoId' => 'required|exists:proyectos,id',
            'fecha' => 'required|date',
            'actividades' => 'required|string|max:10000',
        ], [
            'proyectoId.required' => 'Selecciona un proyecto.',
            'fecha.required' => 'Indica la fecha.',
            'actividades.required' => 'Describe las actividades realizadas.',
        ]);

        $existing = BitacoraDiaria::where('proyecto_id', $this->proyectoId)
            ->where('fecha', $this->fecha)
            ->first();

        if ($existing) {
            $this->errorMessage = 'Ya existe una bitácora para este proyecto en esta fecha.';
            return;
        }

        $personalGpt = collect($this->personal)
            ->filter(fn($p) => $p['presente'])
            ->map(fn($p) => [
                'user_id' => $p['id'],
                'nombre' => $p['nombre'],
                'puesto' => $p['puesto'],
                'horas' => (int)($p['horas'] ?? 0),
                'extra' => (bool)($p['extra'] ?? false),
            ])
            ->values()
            ->toArray();

        $equiposSitio = collect($this->equipos)
            ->map(fn($e) => [
                'id' => $e['id'] ?? null,
                'nombre' => $e['nombre'],
                'tipo' => $e['tipo'],
                'en_sitio' => (bool)($e['enSitio'] ?? false),
                'horometro' => $e['horometro'] ?? '',
                'estado' => $e['estado'] ?? 'operativo',
            ])
            ->values()
            ->toArray();

        $proveedores = collect($this->subcontratistas)
            ->map(fn($s) => [
                'nombre' => $s['nombre'],
                'empresa' => $s['empresa'],
                'horas' => (int)($s['horas'] ?? 0),
            ])
            ->values()
            ->toArray();

        BitacoraDiaria::create([
            'proyecto_id' => $this->proyectoId,
            'fecha' => $this->fecha,
            'relacion_actividades' => $this->actividades,
            'personal_gpt' => $personalGpt,
            'equipos_en_sitio' => $equiposSitio,
            'proveedores_subcontratistas' => $proveedores,
            'vobo_cliente_nombre' => $this->vobo['nombre'] ?: null,
            'vobo_cliente_organizacion' => $this->vobo['organizacion'] ?: null,
            'vobo_cliente_fecha' => $this->vobo['fecha'] ?: null,
            'cargado_por_id' => auth()->id(),
            'firmado_at' => $firmar ? now() : null,
        ]);

        $this->successMessage = $firmar
            ? 'Bitácora firmada y enviada correctamente.'
            : 'Bitácora guardada como borrador.';

        $this->loadBitacoras();
        $this->resetForm();
        $this->vista = 'historial';
    }

    protected function resetForm()
    {
        $this->paso = 0;
        $this->actividades = '';
        $this->subcontratistas = [];
        $this->confirmado = false;
        $this->desviacion = [
            'activa' => false,
            'tipo' => '',
            'severidad' => '',
            'descripcion' => '',
        ];
        $this->vobo = [
            'nombre' => '',
            'organizacion' => '',
            'fecha' => now()->format('Y-m-d'),
        ];

        foreach ($this->personal as $i => $p) {
            $this->personal[$i]['presente'] = true;
            $this->personal[$i]['horas'] = 8;
            $this->personal[$i]['extra'] = false;
        }

        foreach ($this->equipos as $i => $e) {
            $this->equipos[$i]['enSitio'] = true;
            $this->equipos[$i]['horometro'] = '';
            $this->equipos[$i]['estado'] = 'operativo';
        }

        $this->errorMessage = '';
    }

    public function render()
    {
        return view('livewire.proyectos.bitacora-form')->layout('components.layouts.app');
    }
}