<?php

namespace App\Livewire;

use App\Models\Comercial\Cliente;
use App\Models\Comercial\Sublinea;
use App\Models\Proyectos\Proyecto;
use App\Models\User;
use App\Services\Proyectos\SecuenciasService;
use Livewire\Component;

class NuevaOportunidad extends Component
{
    public int $step = 1;

    public string $cliente_id = '';
    public string $sublinea_id = '';
    public string $usuario_final = '';
    public string $sector = '';

    public string $alcance = '';
    public string $plazo_estimado = '';
    public string $fecha_inicio_planeada = '';
    public string $fecha_fin_planeada = '';

    public string $director_dn_id = '';
    public string $notas = '';

    public function mount()
    {
        $this->fecha_inicio_planeada = now()->format('Y-m-d');
    }

    public function nextStep()
    {
        $this->validateStep();
        $this->step = min(3, $this->step + 1);
    }

    public function prevStep()
    {
        $this->step = max(1, $this->step - 1);
    }

    protected function validateStep(): void
    {
        match ($this->step) {
            1 => $this->validate([
                'cliente_id' => 'required|exists:clientes,id',
                'sublinea_id' => 'required|exists:sublineas,id',
                'usuario_final' => 'nullable|string|max:255',
                'sector' => 'nullable|string|max:255',
            ]),
            2 => $this->validate([
                'alcance' => 'required|string|max:5000',
                'plazo_estimado' => 'nullable|string|max:255',
                'fecha_inicio_planeada' => 'nullable|date',
                'fecha_fin_planeada' => 'nullable|date',
            ]),
            default => null,
        };
    }

    public function save()
    {
        $this->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'sublinea_id' => 'required|exists:sublineas,id',
            'alcance' => 'required|string|max:5000',
        ]);

        $secuencias = app(SecuenciasService::class);
        $cp = $secuencias->asignarCp(now()->year);

        $proyecto = Proyecto::create([
            'cp_numero' => $cp,
            'año' => now()->year,
            'cliente_id' => $this->cliente_id,
            'sublinea_id' => $this->sublinea_id,
            'usuario_final' => $this->usuario_final ?: null,
            'sector' => $this->sector ?: null,
            'estado' => 'en_revision',
            'fecha_inicio_planeada' => $this->fecha_inicio_planeada ?: null,
            'fecha_fin_planeada' => $this->fecha_fin_planeada ?: null,
            'director_dn_id' => $this->director_dn_id ?: null,
            'notas' => $this->alcance,
        ]);

        session()->flash('success', "Oportunidad creada: {$cp}");

        return redirect()->route('oportunidades.index');
    }

    public function render()
    {
        return view('livewire.nueva-oportunidad', [
            'clientes' => Cliente::where('activo', true)->get(),
            'sublineas' => Sublinea::all(),
            'directores' => User::role(['director_dn', 'direccion_general', 'gerente_proyectos'])->get(),
        ])->layout('components.layouts.app');
    }
}
