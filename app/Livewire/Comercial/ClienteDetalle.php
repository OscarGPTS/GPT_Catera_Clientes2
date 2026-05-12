<?php

namespace App\Livewire\Comercial;

use App\Models\Comercial\Cliente;
use Livewire\Component;

class ClienteDetalle extends Component
{
    public Cliente $cliente;

    public $showContactoModal = false;
    public $showEditModal = false;

    public $contacto_nombre = '';
    public $contacto_puesto = '';
    public $contacto_email = '';
    public $contacto_telefono = '';
    public $contacto_principal = false;

    public $edit_razon_social = '';
    public $edit_alias_3letras = '';
    public $edit_rfc = '';
    public $edit_sector = '';
    public $edit_segmento = '';

    public $successMessage = '';
    public $errorMessage = '';

    protected function rules()
    {
        return [
            'contacto_nombre' => 'required|string|max:255',
            'contacto_puesto' => 'nullable|string|max:255',
            'contacto_email' => 'nullable|email|max:255',
            'contacto_telefono' => 'nullable|string|max:50',
            'contacto_principal' => 'boolean',
            'edit_razon_social' => 'required|string|max:255',
            'edit_alias_3letras' => 'required|string|max:5',
            'edit_rfc' => 'nullable|string|max:13',
            'edit_sector' => 'nullable|string|max:100',
            'edit_segmento' => 'nullable|string|max:100',
        ];
    }

    public function mount(Cliente $cliente)
    {
        $this->cliente = $cliente;
        $this->loadEditForm();
    }

    public function loadEditForm()
    {
        $this->edit_razon_social = $this->cliente->razon_social;
        $this->edit_alias_3letras = $this->cliente->alias_3letras;
        $this->edit_rfc = $this->cliente->rfc ?? '';
        $this->edit_sector = $this->cliente->sector ?? '';
        $this->edit_segmento = $this->cliente->segmento ?? '';
    }

    public function openContactoModal()
    {
        $this->resetContactoForm();
        $this->showContactoModal = true;
    }

    public function closeContactoModal()
    {
        $this->showContactoModal = false;
        $this->resetContactoForm();
    }

    public function addContacto()
    {
        $this->authorize('update', $this->cliente);

        $this->validateOnly('contacto_nombre');
        $this->validateOnly('contacto_email');

        try {
            if ($this->contacto_principal) {
                $this->cliente->contactos()->update(['principal' => false]);
            }

            $this->cliente->contactos()->create([
                'nombre' => $this->contacto_nombre,
                'puesto' => $this->contacto_puesto ?: null,
                'email' => $this->contacto_email ?: null,
                'telefono' => $this->contacto_telefono ?: null,
                'principal' => $this->contacto_principal,
            ]);

            $this->successMessage = 'Contacto agregado correctamente.';
            $this->closeContactoModal();
            $this->cliente->load('contactos');
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al agregar el contacto: ' . $e->getMessage();
        }
    }

    public function openEditModal()
    {
        $this->loadEditForm();
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
    }

    public function updateCliente()
    {
        $this->authorize('update', $this->cliente);

        $this->validate([
            'edit_razon_social' => 'required|string|max:255',
            'edit_alias_3letras' => 'required|string|max:5',
            'edit_rfc' => 'nullable|string|max:13',
            'edit_sector' => 'nullable|string|max:100',
            'edit_segmento' => 'nullable|string|max:100',
        ]);

        try {
            $this->cliente->update([
                'razon_social' => $this->edit_razon_social,
                'alias_3letras' => strtoupper($this->edit_alias_3letras),
                'rfc' => $this->edit_rfc ? strtoupper($this->edit_rfc) : null,
                'sector' => $this->edit_sector,
                'segmento' => $this->edit_segmento,
            ]);

            $this->successMessage = "Cliente {$this->cliente->razon_social} actualizado correctamente.";
            $this->closeEditModal();
            $this->cliente->refresh();
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al actualizar: ' . $e->getMessage();
        }
    }

    public function render()
    {
        $proyectos = $this->cliente->proyectos()
            ->with(['sublinea', 'cotizaciones'])
            ->latest('updated_at')
            ->get();

        $estadosOportunidad = ['en_revision', 'cotizando', 'cotizado', 'presentado', 'adjudicado_pendiente', 'adjudicado_firmado'];

        $oportunidades = $proyectos->whereIn('estado', $estadosOportunidad)->values();

        $facturadoTotal = $proyectos
            ->whereIn('estado', ['cerrado', 'en_cierre', 'adjudicado_firmado', 'en_ejecucion'])
            ->sum(fn($p) => (float)($p->cotizaciones->max('precio_venta_final') ?? 0));

        $pipelineActivo = $oportunidades
            ->whereIn('estado', ['cotizando', 'cotizado', 'presentado'])
            ->sum(fn($p) => (float)($p->cotizaciones->max('precio_venta_final') ?? 0));

        return view('livewire.comercial.cliente-detalle', [
            'cliente'        => $this->cliente->load('contactos'),
            'proyectos'      => $proyectos,
            'oportunidades'  => $oportunidades,
            'facturadoTotal' => $facturadoTotal,
            'pipelineActivo' => $pipelineActivo,
        ])->layout('components.layouts.app');
    }

    protected function resetContactoForm()
    {
        $this->contacto_nombre = '';
        $this->contacto_puesto = '';
        $this->contacto_email = '';
        $this->contacto_telefono = '';
        $this->contacto_principal = false;
        $this->errorMessage = '';
    }
}