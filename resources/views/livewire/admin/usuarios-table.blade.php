<div x-data="{
    search: $wire.entangle('search'),
    rolFilter: $wire.entangle('rolFilter'),
    estadoFilter: $wire.entangle('estadoFilter'),
    deptoFilter: $wire.entangle('deptoFilter'),
    drawerOpen: $wire.entangle('showDetailDrawer'),
    selectedUserId: $wire.entangle('selectedUserId'),
    inviteModalOpen: $wire.entangle('showInviteModal'),
    detailTab: $wire.entangle('detailTab'),
}" x-on:keydown.escape.window="drawerOpen = false">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-medium text-slate-900">Usuarios</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ $totalActivos }} activos &middot; {{ $totalInvitados }} invitados &middot; {{ $totalSuspendidos }} suspendidos
            </p>
        </div>
        <div class="flex items-center gap-3">
            <x-button variant="ghost" wire:click="syncRh" wire:loading.attr="disabled">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/></svg>
                Sincronizar con RH
            </x-button>
            <x-button variant="primary" wire:click="openInviteModal">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Invitar usuario externo
            </x-button>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mt-6 rounded-lg border border-slate-200 bg-white p-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label class="block text-sm font-medium text-slate-700">Buscar</label>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, email, puesto..."
                    class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-200">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Rol</label>
                <select wire:model.live="rolFilter" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-gpt-600 focus:ring-gpt-200">
                    <option value="">Todos los roles</option>
                    @foreach($allRoles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Estado</label>
                <select wire:model.live="estadoFilter" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-gpt-600 focus:ring-gpt-200">
                    <option value="">Todos</option>
                    <option value="active">Activo</option>
                    <option value="invited">Invitado</option>
                    <option value="suspended">Suspendido</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700">Departamento</label>
                <select wire:model.live="deptoFilter" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-gpt-600 focus:ring-gpt-200">
                    <option value="">Todos</option>
                    @foreach($departamentos as $depto)
                        <option value="{{ $depto }}">{{ $depto }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="mt-4 overflow-hidden rounded-lg border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Nombre</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Puesto</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Rol</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Auth</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($usuarios as $usuario)
                <tr class="cursor-pointer hover:bg-slate-50" wire:click="openDetail({{ $usuario->id }})">
                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gpt-100 text-xs font-semibold text-gpt-800">
                                {{ strtoupper(substr($usuario->name, 0, 2)) }}
                            </div>
                            {{ $usuario->name }}
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $usuario->email }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $usuario->puesto ?? '—' }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        @foreach($usuario->roles as $role)
                            <span class="inline-flex items-center rounded-full bg-gpt-100 px-2.5 py-0.5 text-xs font-medium text-gpt-800">{{ $role->name }}</span>
                        @endforeach
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $this->getStatusBadgeClass($usuario->status) }}">
                            {{ ucfirst($usuario->status) }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        @foreach($usuario->authProviders as $provider)
                            <span title="{{ $provider->provider }}" class="mr-1 text-sm">{{ $this->getProviderIcon($provider->provider) }}</span>
                        @endforeach
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-right">
                        @if($usuario->status === 'active')
                            <x-button variant="ghost" size="sm" wire:click.stop="suspendUser({{ $usuario->id }})" class="text-gpt-red-600">Suspender</x-button>
                        @else
                            <x-button variant="ghost" size="sm" wire:click.stop="activateUser({{ $usuario->id }})" class="text-green-600">Activar</x-button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="border-t border-slate-200 bg-white px-4 py-3">
            {{ $usuarios->links() }}
        </div>
    </div>

    {{-- Invite Modal --}}
    @if($showInviteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60" wire:click.self="closeInviteModal">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h3 class="text-lg font-medium text-slate-900">Invitar usuario externo</h3>
            <p class="mt-1 text-sm text-slate-500">Se creará una cuenta con contraseña temporal y se enviará un email de invitación.</p>

            <div class="mt-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Email *</label>
                    <input type="email" wire:model="inviteEmail" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-200">
                    @error('inviteEmail') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Nombre (opcional)</label>
                    <input type="text" wire:model="inviteName" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Rol *</label>
                    <select wire:model="inviteRole" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-200">
                        <option value="">Seleccionar rol...</option>
                        @foreach($allRoles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('inviteRole') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Departamento (opcional)</label>
                    <input type="text" wire:model="inviteDepartamento" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-200">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-button variant="ghost" wire:click="closeInviteModal">Cancelar</x-button>
                <x-button variant="primary" wire:click="inviteUser" wire:loading.attr="disabled">Enviar invitación</x-button>
            </div>
        </div>
    </div>
    @endif

    {{-- Flash messages --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
            class="fixed bottom-4 right-4 z-50 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 shadow-lg">
            {{ session('success') }}
        </div>
    @endif
</div>