<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Services\Auth\AuthOrchestrator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UsuariosTable extends Component
{
    use WithPagination;

    public string $search = '';
    public string $rolFilter = '';
    public string $estadoFilter = '';
    public string $deptoFilter = '';
    public bool $showInviteModal = false;
    public bool $showDetailDrawer = false;
    public ?int $selectedUserId = null;

    public string $inviteEmail = '';
    public string $inviteName = '';
    public string $inviteRole = '';
    public string $inviteDepartamento = '';

    public string $detailTab = 'general';

    public function mount(): void
    {
        $this->search = request('buscar', '');
        $this->rolFilter = request('rol', '');
        $this->estadoFilter = request('estado', '');
        $this->deptoFilter = request('departamento', '');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRolFilter(): void
    {
        $this->resetPage();
    }

    public function updatingEstadoFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDeptoFilter(): void
    {
        $this->resetPage();
    }

    public function getUsuariosProperty(): LengthAwarePaginator
    {
        $query = User::with(['roles', 'authProviders']);

        if ($this->search) {
            $search = strtolower($this->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(puesto) LIKE ?', ["%{$search}%"]);
            });
        }

        if ($this->rolFilter) {
            $query->whereHas('roles', fn($q) => $q->where('name', $this->rolFilter));
        }

        if ($this->estadoFilter) {
            $query->where('status', $this->estadoFilter);
        }

        if ($this->deptoFilter) {
            $query->where('departamento', $this->deptoFilter);
        }

        return $query->orderBy('name')->paginate(25);
    }

    public function getRolesProperty(): Collection
    {
        return Role::orderBy('name')->get();
    }

    public function getDepartamentosProperty(): Collection
    {
        return User::whereNotNull('departamento')
            ->distinct()
            ->orderBy('departamento')
            ->pluck('departamento');
    }

    public function openDetail(int $userId): void
    {
        $this->selectedUserId = $userId;
        $this->detailTab = 'general';
        $this->showDetailDrawer = true;
    }

    public function closeDetail(): void
    {
        $this->showDetailDrawer = false;
        $this->selectedUserId = null;
    }

    public function openInviteModal(): void
    {
        $this->resetInviteForm();
        $this->showInviteModal = true;
    }

    public function closeInviteModal(): void
    {
        $this->showInviteModal = false;
    }

    public function inviteUser(): void
    {
        $this->validate([
            'inviteEmail' => 'required|email|unique:users,email',
            'inviteRole' => 'required|exists:roles,name',
            'inviteName' => 'nullable|string|max:255',
            'inviteDepartamento' => 'nullable|string|max:255',
        ]);

        try {
            $orchestrator = app(AuthOrchestrator::class);
            $user = $orchestrator->inviteExternalUser(
                $this->inviteEmail,
                $this->inviteRole,
                $this->inviteDepartamento ?: null,
                $this->inviteName ?: null,
            );

            $this->showInviteModal = false;
            session()->flash('success', "Invitación enviada a {$user->email}.");
        } catch (\Exception $e) {
            $this->addError('inviteEmail', $e->getMessage());
        }
    }

    public function updateRole(int $userId, string $role): void
    {
        $user = User::findOrFail($userId);
        $this->authorize('manageRoles', $user);

        $user->syncRoles([$role]);
        session()->flash('success', "Rol actualizado para {$user->name}.");
    }

    public function suspendUser(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->authorize('suspend', $user);

        $user->update(['status' => 'suspended']);
        session()->flash('success', "Usuario {$user->name} suspendido.");
    }

    public function activateUser(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->authorize('update', $user);

        $user->update(['status' => 'active']);
        session()->flash('success', "Usuario {$user->name} activado.");
    }

    public function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'active' => 'bg-green-100 text-green-800',
            'invited' => 'bg-amber-100 text-amber-800',
            'suspended' => 'bg-gpt-red-100 text-gpt-red-800',
            default => 'bg-slate-100 text-slate-800',
        };
    }

    public function getProviderIcon(string $provider): string
    {
        return match ($provider) {
            'auth0' => '🏢',
            'email_password' => '📧',
            'google' => '🔗',
            'microsoft' => '🔗',
            'apple' => '🔗',
            default => '🔐',
        };
    }

    private function resetInviteForm(): void
    {
        $this->inviteEmail = '';
        $this->inviteName = '';
        $this->inviteRole = '';
        $this->inviteDepartamento = '';
    }

    public function render()
    {
        return view('livewire.admin.usuarios-table', [
            'usuarios' => $this->usuarios,
            'totalActivos' => User::where('status', 'active')->count(),
            'totalInvitados' => User::where('status', 'invited')->count(),
            'totalSuspendidos' => User::where('status', 'suspended')->count(),
            'allRoles' => $this->roles,
            'departamentos' => $this->departamentos,
        ]);
    }
}