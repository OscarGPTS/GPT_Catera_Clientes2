<?php

namespace App\Livewire\Admin;

use App\Models\RhRoleMapping;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class RhMappingManager extends Component
{
    public bool $showCreateModal = false;
    public bool $showEditModal = false;
    public ?int $editingId = null;

    public string $puesto_rh = '';
    public string $rol_sistema = '';
    public int $prioridad = 50;
    public string $departamento_filter = '';
    public bool $activo = true;

    public function getMappingsProperty(): Collection
    {
        return RhRoleMapping::orderBy('prioridad', 'desc')->get();
    }

    public function getRolesProperty(): Collection
    {
        return Role::orderBy('name')->get();
    }

    public function getImpactPreviewProperty(): array
    {
        $preview = [];
        foreach ($this->mappings as $mapping) {
            $pattern = str_replace('%', '', $mapping->puesto_rh);
            $query = User::whereRaw('LOWER(puesto) LIKE ?', ["%".strtolower($pattern)."%"]);
            if ($mapping->departamento_filter) {
                $query->where('departamento', $mapping->departamento_filter);
            }
            $preview[$mapping->id] = $query->count();
        }
        return $preview;
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
    }

    public function createMapping(): void
    {
        $this->validate([
            'puesto_rh' => 'required|string|max:255',
            'rol_sistema' => 'required|string|exists:roles,name',
            'prioridad' => 'required|integer|min:0|max:100',
        ]);

        RhRoleMapping::create([
            'puesto_rh' => $this->puesto_rh,
            'rol_sistema' => $this->rol_sistema,
            'prioridad' => $this->prioridad,
            'departamento_filter' => $this->departamento_filter ?: null,
            'activo' => $this->activo,
        ]);

        $this->showCreateModal = false;
        session()->flash('success', 'Regla de mapeo creada correctamente.');
    }

    public function openEditModal(int $id): void
    {
        $mapping = RhRoleMapping::findOrFail($id);
        $this->editingId = $id;
        $this->puesto_rh = $mapping->puesto_rh;
        $this->rol_sistema = $mapping->rol_sistema;
        $this->prioridad = $mapping->prioridad;
        $this->departamento_filter = $mapping->departamento_filter ?? '';
        $this->activo = $mapping->activo;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingId = null;
    }

    public function updateMapping(): void
    {
        $this->validate([
            'puesto_rh' => 'required|string|max:255',
            'rol_sistema' => 'required|string|exists:roles,name',
            'prioridad' => 'required|integer|min:0|max:100',
        ]);

        $mapping = RhRoleMapping::findOrFail($this->editingId);
        $mapping->update([
            'puesto_rh' => $this->puesto_rh,
            'rol_sistema' => $this->rol_sistema,
            'prioridad' => $this->prioridad,
            'departamento_filter' => $this->departamento_filter ?: null,
            'activo' => $this->activo,
        ]);

        $this->showEditModal = false;
        $this->editingId = null;
        session()->flash('success', 'Regla actualizada correctamente.');
    }

    public function deleteMapping(int $id): void
    {
        RhRoleMapping::findOrFail($id)->delete();
        session()->flash('success', 'Regla eliminada.');
    }

    public function toggleActivo(int $id): void
    {
        $mapping = RhRoleMapping::findOrFail($id);
        $mapping->update(['activo' => ! $mapping->activo]);
        session()->flash('success', $mapping->activo ? 'Regla activada.' : 'Regla desactivada.');
    }

    private function resetForm(): void
    {
        $this->puesto_rh = '';
        $this->rol_sistema = '';
        $this->prioridad = 50;
        $this->departamento_filter = '';
        $this->activo = true;
    }

    public function render()
    {
        return view('livewire.admin.rh-mapping-manager', [
            'mappings' => $this->mappings,
            'impactPreview' => $this->impactPreview,
            'allRoles' => $this->roles,
        ]);
    }
}