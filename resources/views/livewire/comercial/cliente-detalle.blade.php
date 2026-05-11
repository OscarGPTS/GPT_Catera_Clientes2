<div>
    @section('title', $cliente->razon_social)

    <div class="space-y-6">
        <div>
            <nav class="flex items-center gap-2 text-sm text-slate-500 mb-2" aria-label="Breadcrumb">
                <a href="{{ route('clientes.index') }}" class="hover:text-slate-700 transition-colors">Clientes</a>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="font-medium text-slate-900">{{ $cliente->razon_social }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-medium text-slate-900">{{ $cliente->razon_social }}</h2>
                @if($cliente->activo)
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Activo</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">Inactivo</span>
                @endif
            </div>
            <p class="mt-1 text-sm text-slate-500">{{ $cliente->alias_3letras }}{{ $cliente->rfc ? ' — ' . $cliente->rfc : '' }}</p>
        </div>

        @if($successMessage)
            <div wire:transition.opacity.duration.500ms class="rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800">{{ $successMessage }}</div>
        @endif

        @if($errorMessage)
            <div wire:transition.opacity.duration.500ms class="rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-800">{{ $errorMessage }}</div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <div class="rounded-lg border border-slate-200 bg-white p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base font-medium text-slate-900">Información del cliente</h3>
                        @can('update', $cliente)
                            <button type="button" wire:click="openEditModal" class="text-sm font-medium text-gpt-600 hover:text-gpt-700 transition-colors">Editar</button>
                        @endcan
                    </div>
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-slate-500">Razón social</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $cliente->razon_social }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-500">Alias</dt>
                            <dd class="mt-1 text-sm">
                                <span class="inline-flex items-center rounded-md bg-gpt-100 px-2 py-0.5 text-xs font-bold text-gpt-800 uppercase">{{ $cliente->alias_3letras }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-500">RFC</dt>
                            <dd class="mt-1 text-sm font-mono text-slate-900">{{ $cliente->rfc ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-500">Sector</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $cliente->sector ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-slate-500">Segmento</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $cliente->segmento ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
                    <div class="border-b border-slate-200 bg-slate-50 px-6 py-4 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-medium text-slate-900">Contactos</h3>
                            <p class="mt-0.5 text-sm text-slate-500">{{ $cliente->contactos->count() }} contacto(s)</p>
                        </div>
                        @can('update', $cliente)
                            <button type="button" wire:click="openContactoModal" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Agregar contacto
                            </button>
                        @endcan
                    </div>
                    @if($cliente->contactos->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nombre</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Puesto</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Email</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Teléfono</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Principal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach($cliente->contactos as $contacto)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900">{{ $contacto->nombre }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $contacto->puesto ?? '—' }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gpt-600">{{ $contacto->email ?? '—' }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $contacto->telefono ?? '—' }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-center">
                                                @if($contacto->principal)
                                                    <svg class="inline h-4 w-4 text-gpt-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                @else
                                                    <span class="text-slate-300">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="px-6 py-12 text-center">
                            <p class="text-sm text-slate-500">Sin contactos registrados.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="space-y-6">
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <h3 class="text-sm font-semibold text-slate-900 mb-3">Proyectos relacionados</h3>
                    @if($cliente->proyectos->count() > 0)
                        <ul class="space-y-2">
                            @foreach($cliente->proyectos as $p)
                                <li>
                                    <a href="{{ route('oportunidades.show', $p) }}" class="flex items-center justify-between p-2 rounded-md hover:bg-slate-50 transition-colors">
                                        <div>
                                            <span class="text-sm font-mono font-medium text-slate-900">{{ $p->cp_numero ?? '—' }}</span>
                                            <p class="text-xs text-slate-500">{{ $p->tech_reference ?? '' }}</p>
                                        </div>
                                        <x-badge :status="$p->estado" />
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-slate-500">Sin proyectos relacionados.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Add Contact Modal --}}
    @if($showContactoModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50" wire:click.self="closeContactoModal">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <h3 class="text-lg font-medium text-slate-900 mb-4">Agregar contacto</h3>
            <form wire:submit.prevent="addContacto">
                <div class="space-y-4">
                    <div>
                        <label for="contacto_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre *</label>
                        <input type="text" wire:model="contacto_nombre" id="contacto_nombre" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600 @error('contacto_nombre') border-red-300 @enderror">
                        @error('contacto_nombre') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="contacto_puesto" class="block text-sm font-medium text-slate-700 mb-1">Puesto</label>
                        <input type="text" wire:model="contacto_puesto" id="contacto_puesto" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                    </div>
                    <div>
                        <label for="contacto_email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" wire:model="contacto_email" id="contacto_email" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600 @error('contacto_email') border-red-300 @enderror">
                        @error('contacto_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="contacto_telefono" class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                        <input type="text" wire:model="contacto_telefono" id="contacto_telefono" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                    </div>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="contacto_principal" class="h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                        <span class="text-sm text-slate-700">Contacto principal</span>
                    </label>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="closeContactoModal" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-700">Agregar contacto</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Edit Client Modal --}}
    @if($showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50" wire:click.self="closeEditModal">
        <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
            <h3 class="text-lg font-medium text-slate-900 mb-4">Editar cliente</h3>
            <form wire:submit.prevent="updateCliente">
                <div class="space-y-4">
                    <div>
                        <label for="edit_razon_social" class="block text-sm font-medium text-slate-700 mb-1">Razón social *</label>
                        <input type="text" wire:model="edit_razon_social" id="edit_razon_social" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600 @error('edit_razon_social') border-red-300 @enderror">
                        @error('edit_razon_social') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_alias_3letras" class="block text-sm font-medium text-slate-700 mb-1">Alias *</label>
                            <input type="text" wire:model="edit_alias_3letras" id="edit_alias_3letras" maxlength="5" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600 uppercase @error('edit_alias_3letras') border-red-300 @enderror">
                            @error('edit_alias_3letras') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="edit_rfc" class="block text-sm font-medium text-slate-700 mb-1">RFC</label>
                            <input type="text" wire:model="edit_rfc" id="edit_rfc" maxlength="13" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600 uppercase @error('edit_rfc') border-red-300 @enderror">
                            @error('edit_rfc') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_sector" class="block text-sm font-medium text-slate-700 mb-1">Sector</label>
                            <select wire:model="edit_sector" id="edit_sector" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <option value="">Seleccionar...</option>
                                <option value="Gobierno">Gobierno</option>
                                <option value="Energia">Energía</option>
                                <option value="Industrial">Industrial</option>
                                <option value="Privado">Privado</option>
                            </select>
                        </div>
                        <div>
                            <label for="edit_segmento" class="block text-sm font-medium text-slate-700 mb-1">Segmento</label>
                            <select wire:model="edit_segmento" id="edit_segmento" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <option value="">Seleccionar...</option>
                                <option value="A">A - Estratégico</option>
                                <option value="B">B - Crecimiento</option>
                                <option value="C">C - Operativo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="closeEditModal" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-700">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>