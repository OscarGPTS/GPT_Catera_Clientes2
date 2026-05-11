<div>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-medium text-slate-900">BOM / BOE</h2>
            <p class="mt-1 text-sm text-slate-500">Bill of Materials / Equipment del proyecto</p>
        </div>
    </x-slot>

    {{-- Project selector + filters --}}
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <div class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="proyectoId" class="block text-sm font-medium text-slate-700">Proyecto</label>
                <select id="proyectoId" wire:model.live="proyectoId"
                        class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                    <option value="">Seleccionar proyecto...</option>
                    @foreach($this->proyectos as $p)
                        <option value="{{ $p->id }}">{{ $p->cp_numero }} — {{ $p->cliente->razon_social ?? 'Sin cliente' }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="tipoFiltro" class="block text-sm font-medium text-slate-700">Tipo</label>
                <select id="tipoFiltro" wire:model.live="tipoFiltro"
                        class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                    <option value="">Todos</option>
                    <option value="BOM">BOM</option>
                    <option value="BOE">BOE</option>
                </select>
            </div>
            <div>
                <label for="statusFiltro" class="block text-sm font-medium text-slate-700">Estado</label>
                <select id="statusFiltro" wire:model.live="statusFiltro"
                        class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                    <option value="">Todos</option>
                    @foreach($itemStatusLabels as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @can('editar bom boe')
                @if($proyectoId)
                    <button wire:click="$set('showCreateModal', true)"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Agregar item
                    </button>
                @endif
            @endcan
        </div>
    </div>

    @if($proyectoId && $items)
        {{-- Items table --}}
        <div class="mt-6 rounded-lg border border-slate-200 bg-white overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Descripción</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Cant.</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Unidad</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Req.</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Resp.</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($items as $item)
                            @if($editItemId === $item->id)
                                <tr class="bg-gpt-50">
                                    <td class="px-4 py-3 text-sm font-medium">{{ $item->tipo }}</td>
                                    <td class="px-4 py-3">
                                        <input type="text" wire:model="descripcion" class="w-full rounded-md border border-slate-200 px-2 py-1 text-sm focus:border-gpt-600 focus:ring-gpt-600">
                                    </td>
                                    <td class="px-4 py-3">
                                        <input type="number" wire:model="cantidad" step="0.01" class="w-20 rounded-md border border-slate-200 px-2 py-1 text-sm text-right focus:border-gpt-600 focus:ring-gpt-600">
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-500">{{ $item->unidad }}</td>
                                    <td class="px-4 py-3">
                                        <select wire:model="status_item" class="rounded-md border border-slate-200 px-2 py-1 text-sm focus:border-gpt-600 focus:ring-gpt-600">
                                            @foreach($itemStatusLabels as $key => $label)
                                                <option value="{{ $key }}">{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-500">{{ $item->fecha_requerida?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-slate-500">{{ $item->responsable->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right space-x-2">
                                        <button wire:click="update" class="text-sm font-medium text-gpt-600 hover:text-gpt-700">Guardar</button>
                                        <button wire:click="$set('editItemId', null)" class="text-sm text-slate-500 hover:text-slate-700">Cancelar</button>
                                    </td>
                                </tr>
                            @else
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $item->tipo === 'BOM' ? 'bg-blue-100 text-blue-800' : 'bg-gpt-100 text-gpt-800' }}">{{ $item->tipo }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-slate-900">{{ $item->descripcion }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-slate-900">{{ number_format($item->cantidad, 2) }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $item->unidad }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $itemStatusColors[$item->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $itemStatusLabels[$item->status] ?? $item->status }}</span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500">{{ $item->fecha_requerida?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500">{{ $item->responsable->name ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-right space-x-2">
                                        @can('editar bom boe')
                                            <button wire:click="edit({{ $item->id }})" class="text-sm font-medium text-gpt-600 hover:text-gpt-700">Editar</button>
                                            <button wire:click="delete({{ $item->id }})" wire:confirm="¿Eliminar este item?" class="text-sm font-medium text-gpt-red-600 hover:text-gpt-red-700">Eliminar</button>
                                        @endcan
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center">
                                    <x-empty-state
                                        title="Sin items"
                                        description="Agrega materiales y equipos al BOM/BOE de este proyecto."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $items->links() }}
            </div>
        </div>
    @else
        <div class="mt-6 rounded-lg border border-slate-200 bg-white p-6">
            <x-empty-state
                title="Selecciona un proyecto"
                description="Elige un proyecto en ejecución para ver y gestionar su BOM/BOE."
            />
        </div>
    @endif

    {{-- Create modal --}}
    @if($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="$set('showCreateModal', false)">
            <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl" wire:click.stop>
                <h3 class="text-lg font-medium text-slate-900">Agregar item al BOM/BOE</h3>
                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tipo *</label>
                        <select wire:model="tipo" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-600">
                            <option value="BOM">BOM — Bill of Materials</option>
                            <option value="BOE">BOE — Bill of Equipment</option>
                        </select>
                        @error('tipo') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Descripción *</label>
                        <input type="text" wire:model="descripcion" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-600" placeholder="Descripción del material o equipo">
                        @error('descripcion') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Cantidad *</label>
                            <input type="number" wire:model="cantidad" step="0.01" min="0" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-600">
                            @error('cantidad') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Unidad *</label>
                            <input type="text" wire:model="unidad" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-600" placeholder="pza, kg, m...">
                            @error('unidad') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Estado *</label>
                        <select wire:model="status_item" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-600">
                            @foreach($itemStatusLabels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status_item') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Fecha requerida</label>
                        <input type="date" wire:model="fecha_requerida" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-600">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Observaciones</label>
                        <textarea wire:model="observaciones" rows="2" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-600" placeholder="Notas adicionales..."></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showCreateModal', false)" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</button>
                    <button wire:click="store" class="rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">Guardar item</button>
                </div>
            </div>
        </div>
    @endif
</div>