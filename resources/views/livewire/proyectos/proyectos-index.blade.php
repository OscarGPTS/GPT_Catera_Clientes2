<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-medium text-slate-900">Proyectos</h2>
                <p class="mt-1 text-sm text-slate-500">Proyectos en ejecución y seguimiento</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('oportunidades.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                    Pipeline
                </a>
                <a href="{{ route('oportunidades.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Nuevo proyecto
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Filtros reactivos --}}
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="buscar" class="block text-sm font-medium text-slate-700">Buscar</label>
                <input type="text" id="buscar" wire:model.live.debounce.300ms="buscar" placeholder="CP, nombre o cliente..."
                       class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-600">
            </div>
            <div>
                <label for="estado" class="block text-sm font-medium text-slate-700">Estado</label>
                <select id="estado" wire:model.live="estado"
                        class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                    <option value="">Todos</option>
                    @foreach($this->estados as $estadoOpt)
                        <option value="{{ $estadoOpt['value'] }}">{{ $estadoOpt['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="clienteId" class="block text-sm font-medium text-slate-700">Cliente</label>
                <select id="clienteId" wire:model.live="clienteId"
                        class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                    <option value="">Todos</option>
                    @foreach($this->clientes as $c)
                        <option value="{{ $c->id }}">{{ $c->razon_social }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="sublineaId" class="block text-sm font-medium text-slate-700">Sublínea</label>
                <select id="sublineaId" wire:model.live="sublineaId"
                        class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                    <option value="">Todas</option>
                    @foreach($this->sublineas as $sub)
                        <option value="{{ $sub->id }}">{{ $sub->nombre }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @if($buscar || $estado || $clienteId || $sublineaId)
            <div class="mt-3">
                <button wire:click="limpiarFiltros" class="text-sm text-gpt-600 hover:text-gpt-700 transition-colors">Limpiar filtros</button>
            </div>
        @endif
    </div>

    {{-- Tabla de proyectos --}}
    <div class="mt-6 rounded-lg border border-slate-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">CP</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Proyecto</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Sublínea</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Monto</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Avance</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Líder</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($proyectos as $proyecto)
                        @php
                            $monto = $proyecto->cotizaciones->max('precio_venta_final') ?? 0;
                            $avance = $proyecto->libroProyecto?->avance_porcentaje ?? 0;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors cursor-pointer" wire:navigate href="{{ route('oportunidades.show', $proyecto) }}">
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-mono font-medium text-slate-900">{{ $proyecto->cp_numero }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-900 max-w-[200px] truncate">{{ $proyecto->tech_reference ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $proyecto->cliente->razon_social ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $proyecto->sublinea->nombre ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <x-badge :status="$proyecto->estado" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-slate-900">$ {{ number_format($monto, 2, '.', ',') }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-gpt-600 transition-all" style="width: {{ min($avance, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs text-slate-500">{{ $avance }}%</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $proyecto->gerenteProyectos->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('oportunidades.show', $proyecto) }}" class="text-sm font-medium text-gpt-600 hover:text-gpt-700 transition-colors" wire:navigate>
                                    Ver detalle
                                    <span aria-hidden="true">&rarr;</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <x-empty-state
                                    title="Sin proyectos"
                                    description="No se encontraron proyectos en ejecución con los filtros seleccionados."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-200 px-4 py-3">
            {{ $proyectos->links() }}
        </div>
    </div>
</div>