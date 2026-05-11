<?php

namespace App\Livewire\Proyectos;

use App\Models\Proyectos\Proyecto;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class AdjudicacionForm extends Component
{
    use AuthorizesRequests;

    public Proyecto $proyecto;

    public $fecha_inicio_planeada;
    public $fecha_fin_planeada;
    public $metodo_distribucion_plurianual = 'dias_naturales';
    public $gerente_proyectos_id = '';
    public $gerente_operaciones_id = '';
    public $notas = '';

    public $showFirmarModal = false;
    public $notasFirma = '';

    protected function rules()
    {
        return [
            'fecha_inicio_planeada' => 'required|date',
            'fecha_fin_planeada' => 'required|date|after:fecha_inicio_planeada',
            'metodo_distribucion_plurianual' => 'required|in:dias_naturales,hitos',
            'gerente_proyectos_id' => 'required|exists:users,id',
            'gerente_operaciones_id' => 'nullable|exists:users,id',
            'notas' => 'nullable|string|max:5000',
        ];
    }

    public function mount()
    {
        $this->authorize('adjudicar', $this->proyecto);

        $this->proyecto->load(['cliente.contactos', 'sublinea', 'gerenteProyectos', 'cotizaciones.partidas']);

        $this->fecha_inicio_planeada = $this->proyecto->fecha_inicio_planeada?->format('Y-m-d');
        $this->fecha_fin_planeada = $this->proyecto->fecha_fin_planeada?->format('Y-m-d');
        $this->metodo_distribucion_plurianual = $this->proyecto->metodo_distribucion_plurianual ?? 'dias_naturales';
        $this->gerente_proyectos_id = $this->proyecto->gerente_proyectos_id ?? '';
        $this->gerente_operaciones_id = $this->proyecto->gerente_operaciones_id ?? '';
    }

    public function getGerentesProperty()
    {
        return User::role('gerente_proyectos')->where('status', 'active')->orderBy('name')->get();
    }

    public function getGerentesOpsProperty()
    {
        return User::role('gerente_operaciones')->where('status', 'active')->orderBy('name')->get();
    }

    public function adjudicar()
    {
        $this->authorize('adjudicar', $this->proyecto);

        $validated = $this->validate();

        $this->proyecto->update([
            'estado' => 'adjudicado_pendiente',
            'fecha_inicio_planeada' => $validated['fecha_inicio_planeada'],
            'fecha_fin_planeada' => $validated['fecha_fin_planeada'],
            'metodo_distribucion_plurianual' => $validated['metodo_distribucion_plurianual'],
            'gerente_proyectos_id' => $validated['gerente_proyectos_id'],
            'gerente_operaciones_id' => $validated['gerente_operaciones_id'] ?: null,
            'notas' => ($this->proyecto->notas ? $this->proyecto->notas . "\n" : '') . 'Adjudicado: ' . ($validated['notas'] ?? ''),
        ]);

        $this->proyecto->eventos()->create([
            'tipo' => 'adjudicado',
            'user_id' => auth()->id(),
            'comentario' => 'Proyecto adjudicado. GP asignado: ' . $this->proyecto->gerenteProyectos->name,
        ]);

        session()->flash('success', 'Proyecto adjudicado correctamente. Pendiente de firma.');
        return redirect()->route('oportunidades.show', $this->proyecto);
    }

    public function confirmarFirma()
    {
        $this->authorize('adjudicar', $this->proyecto);

        $this->validate([
            'notasFirma' => 'nullable|string|max:5000',
        ]);

        $this->proyecto->update([
            'estado' => 'adjudicado_firmado',
            'notas' => ($this->proyecto->notas ? $this->proyecto->notas . "\n" : '') . 'Firma confirmada: ' . ($this->notasFirma ?? ''),
        ]);

        $this->proyecto->eventos()->create([
            'tipo' => 'firma_adjudicacion',
            'user_id' => auth()->id(),
            'comentario' => 'Firma de adjudicación confirmada.',
        ]);

        session()->flash('success', 'Firma de adjudicación confirmada. Proyecto listo para ejecución.');
        return redirect()->route('oportunidades.show', $this->proyecto);
    }

    public function render()
    {
        return view('livewire.proyectos.adjudicacion-form', [
            'gerentes' => $this->gerentes,
            'gerentesOps' => $this->gerentesOps,
        ])->layout('components.layouts.app');
    }
}