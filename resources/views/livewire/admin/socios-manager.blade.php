<div>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h2 class="text-xl sm:text-2xl font-medium text-slate-900">Socios</h2>
        <x-button variant="primary" wire:click="openAddModal">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Agregar email
        </x-button>
    </div>

    <div class="mt-8">
        <h3 class="text-lg font-medium text-slate-900">Usuarios con estatus de socio</h3>

        <div class="mt-4 overflow-hidden rounded-lg border border-slate-200 bg-white">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Nombre</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Puesto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Override manual</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($socios as $socio)
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900">{{ $socio->name }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $socio->email }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $socio->puesto ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <button wire:click="toggleOverride({{ $socio->id }})"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $socio->es_socio_override === true ? 'bg-gpt-600' : ($socio->es_socio_override === false ? 'bg-gpt-red-500' : 'bg-slate-300') }}">
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $socio->es_socio_override === true ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                            @if($socio->es_socio_override === null)<span class="ml-2 text-xs text-slate-400">Auto</span>@endif
                        </td>
                        <td class="px-4 py-3">
                            @if($socio->es_socio)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Socio</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">No socio</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    @if($socios->isEmpty())
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400">No hay usuarios con estatus de socio</td></tr>
                    @endif
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <div class="mt-8">
        <h3 class="text-lg font-medium text-slate-900">Lista de emails autorizados</h3>
        <p class="mt-1 text-sm text-slate-500">Emails que serán reconocidos como socios automáticamente (D2).</p>

        <div class="mt-4 overflow-hidden rounded-lg border border-slate-200 bg-white">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Notas</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Agregado por</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Fecha</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach($allowlist as $entry)
                    <tr class="hover:bg-slate-50">
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900">{{ $entry->email }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $entry->notes ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $entry->added_by ?? '—' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500">{{ $entry->added_at?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="removeAllowlist({{ $entry->id }})" wire:confirm="¿Eliminar este email?"
                                class="text-sm text-gpt-red-600 hover:text-gpt-red-700">Eliminar</button>
                        </td>
                    </tr>
                    @endforeach
                    @if($allowlist->isEmpty())
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400">No hay emails en la lista</td></tr>
                    @endif
                </tbody>
            </table>
            </div>
        </div>
    </div>

    {{-- Add Modal --}}
    @if($showAddModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60" wire:click.self="closeAddModal">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h3 class="text-lg font-medium text-slate-900">Agregar email a lista de socios</h3>
            <div class="mt-4 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Email *</label>
                    <input type="email" wire:model="addEmail" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-200">
                    @error('addEmail') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Notas</label>
                    <textarea wire:model="addNotes" rows="2" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-200"></textarea>
                </div>
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <x-button variant="ghost" wire:click="closeAddModal">Cancelar</x-button>
                <x-button variant="primary" wire:click="addAllowlist" wire:loading.attr="disabled">Agregar</x-button>
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