<?php

namespace App\Livewire\Comercial;

use App\Models\Comercial\Cliente;
use Livewire\Component;
use Livewire\WithPagination;

class ClientesIndex extends Component
{
    use WithPagination;

    public $buscar = '';
    public $sector = '';
    public $incluirInactivos = false;

    public $showCreateModal = false;

    public $razon_social = '';
    public $alias_3letras = '';
    public $rfc = '';
    public $nuevoSector = '';
    public $nuevoSegmento = '';

    public $successMessage = '';
    public $errorMessage = '';

    protected $rules = [
        'razon_social' => 'required|string|max:255',
        'alias_3letras' => 'required|string|max:5|unique:clientes,alias_3letras',
        'rfc' => 'nullable|string|max:13|unique:clientes,rfc',
        'nuevoSector' => 'nullable|string|max:100',
        'nuevoSegmento' => 'nullable|string|max:100',
    ];

    protected $messages = [
        'razon_social.required' => 'La razón social es obligatoria.',
        'alias_3letras.required' => 'El alias es obligatorio.',
        'alias_3letras.unique' => 'Este alias ya está en uso.',
        'rfc.unique' => 'Este RFC ya está registrado.',
    ];

    public function mount()
    {
        $this->buscar = request()->query('buscar', '');
        $this->sector = request()->query('sector', '');
    }

    public function updatingBuscar()
    {
        $this->resetPage();
    }

    public function updatingSector()
    {
        $this->resetPage();
    }

    public function updatingIncluirInactivos()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    public function store()
    {
        $this->authorize('create', Cliente::class);

        $this->validate();

        try {
            Cliente::create([
                'razon_social' => $this->razon_social,
                'alias_3letras' => strtoupper($this->alias_3letras),
                'rfc' => $this->rfc ? strtoupper($this->rfc) : null,
                'sector' => $this->nuevoSector,
                'segmento' => $this->nuevoSegmento,
                'activo' => true,
            ]);

            $this->successMessage = "Cliente {$this->razon_social} creado correctamente.";
            $this->closeCreateModal();
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al crear el cliente: ' . $e->getMessage();
        }
    }

    public function getSectoresProperty()
    {
        return Cliente::where('activo', true)
            ->whereNotNull('sector')
            ->distinct()
            ->pluck('sector')
            ->sort()
            ->values();
    }

    public function getClientesProperty()
    {
        $query = Cliente::with('contactos');

        if ($this->buscar) {
            $query->where(function ($q) {
                $q->whereRaw('LOWER(razon_social) LIKE ?', ["%".strtolower($this->buscar)."%"])
                    ->orWhereRaw('LOWER(alias_3letras) LIKE ?', ["%".strtolower($this->buscar)."%"])
                    ->orWhereRaw('LOWER(rfc) LIKE ?', ["%".strtolower($this->buscar)."%"]);
            });
        }

        if ($this->sector) {
            $query->where('sector', $this->sector);
        }

        if (!$this->incluirInactivos) {
            $query->where('activo', true);
        }

        return $query->orderBy('razon_social')->paginate(25);
    }

    public function render()
    {
        return view('livewire.comercial.clientes-index', [
            'sectores' => $this->sectores,
        ])->layout('components.layouts.app');
    }

    protected function resetCreateForm()
    {
        $this->razon_social = '';
        $this->alias_3letras = '';
        $this->rfc = '';
        $this->nuevoSector = '';
        $this->nuevoSegmento = '';
        $this->errorMessage = '';
    }
}