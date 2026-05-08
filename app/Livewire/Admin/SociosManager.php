<?php

namespace App\Livewire\Admin;

use App\Models\SocioAllowlist;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Component;

class SociosManager extends Component
{
    public bool $showAddModal = false;
    public string $addEmail = '';
    public string $addNotes = '';

    public function getAllowlistProperty(): Collection
    {
        return SocioAllowlist::orderBy('added_at', 'desc')->get();
    }

    public function getSociosProperty(): Collection
    {
        return User::where('es_socio', true)
            ->orWhereNotNull('es_socio_override')
            ->with('roles')
            ->orderBy('name')
            ->get();
    }

    public function openAddModal(): void
    {
        $this->resetValidation();
        $this->addEmail = '';
        $this->addNotes = '';
        $this->showAddModal = true;
    }

    public function closeAddModal(): void
    {
        $this->showAddModal = false;
    }

    public function addAllowlist(): void
    {
        $this->validate([
            'addEmail' => 'required|email|unique:socios_allowlist,email',
        ]);

        SocioAllowlist::create([
            'email' => strtolower($this->addEmail),
            'notes' => $this->addNotes,
            'added_by' => auth()->user()->name,
        ]);

        $user = User::where('email', $this->addEmail)->first();
        if ($user) {
            $socioResolver = app(\App\Services\Auth\SocioResolver::class);
            $socioResolver->refreshSocioStatus($user);
        }

        $this->showAddModal = false;
        session()->flash('success', 'Email agregado a la lista de socios.');
    }

    public function removeAllowlist(int $allowlistId): void
    {
        $entry = SocioAllowlist::findOrFail($allowlistId);
        $email = $entry->email;
        $entry->delete();

        $user = User::where('email', $email)->first();
        if ($user) {
            \Illuminate\Support\Facades\Cache::forget("socio_resolver:{$email}");
            $socioResolver = app(\App\Services\Auth\SocioResolver::class);
            $socioResolver->refreshSocioStatus($user);
        }

        session()->flash('success', 'Email removido de la lista de socios.');
    }

    public function toggleOverride(int $userId): void
    {
        $user = User::findOrFail($userId);
        $socioResolver = app(\App\Services\Auth\SocioResolver::class);
        $socioResolver->refreshSocioStatus($user);

        session()->flash('success', 'Estado de socio actualizado.');
    }

    public function render()
    {
        return view('livewire.admin.socios-manager', [
            'allowlist' => $this->allowlist,
            'socios' => $this->socios,
        ]);
    }
}