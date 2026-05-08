<div>
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-medium text-slate-900">Mapeo RH → Roles</h2>
            <p class="mt-1 text-sm text-slate-500">Configura cómo los puestos del sistema RH se asignan a roles de la plataforma.</p>
        </div>
        <x-button variant="primary" wire:click="openCreateModal">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Nueva regla
        </x-button>
    </div>

    <div class="mt-6 overflow-hidden rounded-lg border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Prioridad</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Puesto RH (patrón)</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Rol del sistema</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Filtro depto.</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Usuarios afectados</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Estado</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @foreach($mappings as $mapping)
                <tr class="hover:bg-slate-50 {{ $mapping->activo ? '' : 'opacity-50' }}">
                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900">{{ $mapping->prioridad }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-700"><code class="rounded bg-slate-100 px-1 py-0.5 text-xs">{{ $mapping->puesto_rh }}</code></td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="inline-flex items-center rounded-full bg-gpt-100 px-2.5 py-0.5 text-xs font-medium text-gpt-800">{{ $mapping->rol_sistema }}</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $mapping->departamento_filter ?? '—' }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $impactPreview[$mapping->id] ?? 0 }} usuarios</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <button wire:click="toggleActivo({{ $mapping->id }})"
                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $mapping->activo ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $mapping->activo ? 'Activo' : 'Inactivo' }}
                        </button>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-right space-x-2">
                        <button wire:click="openEditModal({{ $mapping->id }})" class="text-sm text-gpt-600 hover:text-gpt-700">Editar</button>
                        <button wire:click="deleteMapping({{ $mapping->id }})" wire:confirm="¿Eliminar esta regla?" class="text-sm text-gpt-red-600 hover:text-gpt-red-700">Eliminar</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Create/Edit Modal --}}
    @if($showCreateModal || $showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60" wire:click.self="{{ $showEditModal ? 'closeEditModal' : 'closeCreateModal' }}">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h3 class="text-lg font-medium text-slate-900">{{ $showEditModal ? 'Editar regla' : 'Nueva regla de mapeo' }}</h3>
            <div class="mt-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Puesto RH (patrón LIKE) *</label>
                    <input type="text" wire:model="puesto_rh" placeholder="Ej: %Gerente de Proyectos%" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-200">
                    @error('puesto_rh') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Rol del sistema *</label>
                    <select wire:model="rol_sistema" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-200">
                        <option value="">Seleccionar rol...</option>
                        @foreach($allRoles as $role)
                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                        @endforeach
                    </select>
                    @error('rol_sistema') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Prioridad (0-100)</label>
                    <input type="number" wire:model="prioridad" min="0" max="100" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-200">
                    @error('prioridad') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Filtro de departamento</label>
                    <input type="text" wire:model="departamento_filter" placeholder="Ej: Proyectos (opcional)" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-200">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" wire:model="activo" id="activo" class="rounded border-slate-300 text-gpt-600 focus:ring-gpt-200">
                    <label for="activo" class="text-sm text-slate-700">Activo</label>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <x-button variant="ghost" wire:click="{{ $showEditModal ? 'closeEditModal' : 'closeCreateModal' }}">Cancelar</x-button>
                <x-button variant="primary" wire:click="{{ $showEditModal ? 'updateMapping' : 'createMapping' }}" wire:loading.attr="disabled">
                    {{ $showEditModal ? 'Guardar cambios' : 'Crear regla' }}
                </x-button>
            </div>
        </div>
    </div>
    @endif

    @if(session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
            class="fixed bottom-4 right-4 z-50 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 shadow-lg">
            {{ session('success') }}
        </div>
    @endif
</div>