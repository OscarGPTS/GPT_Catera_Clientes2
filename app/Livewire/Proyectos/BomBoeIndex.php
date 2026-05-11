<?php

namespace App\Livewire\Proyectos;

use App\Models\Proyectos\BomBoeItem;
use App\Models\Proyectos\Proyecto;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class BomBoeIndex extends Component
{
    use WithPagination;
    use AuthorizesRequests;

    public $proyectoId = '';
    public $tipoFiltro = '';
    public $statusFiltro = '';

    public $showCreateModal = false;
    public $editItemId = null;

    public $tipo = 'BOM';
    public $descripcion = '';
    public $cantidad = '';
    public $unidad = '';
    public $status_item = 'por_comprar';
    public $fecha_requerida = '';
    public $observaciones = '';

    protected $rules = [
        'tipo' => 'required|in:BOM,BOE',
        'descripcion' => 'required|string|max:500',
        'cantidad' => 'required|numeric|min:0',
        'unidad' => 'required|string|max:50',
        'status_item' => 'required|in:en_almacen,por_afilar,por_fabricar,por_comprar,en_transito,entregado',
        'fecha_requerida' => 'nullable|date',
        'observaciones' => 'nullable|string|max:500',
    ];

    public function updatingProyectoId()
    {
        $this->resetPage();
    }

    public function updatingTipoFiltro()
    {
        $this->resetPage();
    }

    public function updatingStatusFiltro()
    {
        $this->resetPage();
    }

    public function getProyectosProperty()
    {
        return Proyecto::whereIn('estado', ['en_ejecucion', 'en_cierre', 'adjudicado_firmado'])
            ->with(['cliente', 'sublinea'])
            ->orderBy('cp_numero')
            ->get();
    }

    public function store()
    {
        $this->authorize('editar bom boe');

        $this->validate();
        $this->validate(['proyectoId' => 'required|exists:proyectos,id']);

        BomBoeItem::create([
            'proyecto_id' => $this->proyectoId,
            'tipo' => $this->tipo,
            'descripcion' => $this->descripcion,
            'cantidad' => $this->cantidad,
            'unidad' => $this->unidad,
            'status' => $this->status_item,
            'fecha_requerida' => $this->fecha_requerida ?: null,
            'observaciones' => $this->observaciones,
            'responsable_id' => auth()->id(),
        ]);

        $this->reset(['showCreateModal', 'tipo', 'descripcion', 'cantidad', 'unidad', 'status_item', 'fecha_requerida', 'observaciones']);
        $this->dispatch('item-saved');
    }

    public function edit($itemId)
    {
        $item = BomBoeItem::findOrFail($itemId);
        $this->editItemId = $item->id;
        $this->descripcion = $item->descripcion;
        $this->cantidad = $item->cantidad;
        $this->status_item = $item->status;
    }

    public function update()
    {
        $this->authorize('editar bom boe');

        $this->validate([
            'descripcion' => 'required|string|max:500',
            'cantidad' => 'required|numeric|min:0',
            'status_item' => 'required|in:en_almacen,por_afilar,por_fabricar,por_comprar,en_transito,entregado',
        ]);

        $item = BomBoeItem::findOrFail($this->editItemId);
        $item->update([
            'descripcion' => $this->descripcion,
            'cantidad' => $this->cantidad,
            'status' => $this->status_item,
        ]);

        $this->reset('editItemId', 'descripcion', 'cantidad', 'status_item');
        $this->dispatch('item-saved');
    }

    public function delete($itemId)
    {
        $this->authorize('editar bom boe');
        BomBoeItem::findOrFail($itemId)->delete();
        $this->dispatch('item-deleted');
    }

    public function render()
    {
        $itemStatusLabels = [
            'en_almacen' => 'En almacén',
            'por_afilar' => 'Por afilar',
            'por_fabricar' => 'Por fabricar',
            'por_comprar' => 'Por comprar',
            'en_transito' => 'En tránsito',
            'entregado' => 'Entregado',
        ];

        $itemStatusColors = [
            'en_almacen' => 'bg-green-100 text-green-800',
            'por_afilar' => 'bg-amber-100 text-amber-800',
            'por_fabricar' => 'bg-blue-100 text-blue-800',
            'por_comprar' => 'bg-gpt-100 text-gpt-800',
            'en_transito' => 'bg-amber-100 text-amber-800',
            'entregado' => 'bg-slate-100 text-slate-700',
        ];

        if ($this->proyectoId) {
            $query = BomBoeItem::where('proyecto_id', $this->proyectoId)
                ->with('responsable');

            if ($this->tipoFiltro) {
                $query->where('tipo', $this->tipoFiltro);
            }
            if ($this->statusFiltro) {
                $query->where('status', $this->statusFiltro);
            }

            $items = $query->orderBy('tipo')->orderBy('descripcion')->paginate(25, ['*'], 'items');
        } else {
            $items = null;
        }

        return view('livewire.proyectos.bom-boe-index', [
            'items' => $items,
            'itemStatusLabels' => $itemStatusLabels,
            'itemStatusColors' => $itemStatusColors,
        ])->layout('components.layouts.app');
    }
}