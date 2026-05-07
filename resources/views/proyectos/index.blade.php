<x-layouts.app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-medium text-slate-900">Proyectos</h2>
                <p class="mt-1 text-sm text-slate-500">Proyectos en ejecución y seguimiento</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('proyectos.oportunidades') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                    Pipeline
                </a>
                <a href="{{ route('proyectos.nueva-oportunidad') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Nuevo proyecto
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Filtros --}}
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <form method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="buscar" class="block text-sm font-medium text-slate-700">Buscar</label>
                <input type="text" id="buscar" name="buscar" value="{{ request('buscar') }}" placeholder="CP, nombre o cliente..." class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-600">
            </div>
            <div>
                <label for="estado" class="block text-sm font-medium text-slate-700">Estado</label>
                <select id="estado" name="estado" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                    <option value="">Todos</option>
                    <option value="en_ejecucion" @selected(request('estado') == 'en_ejecucion')>En ejecución</option>
                    <option value="en_pausa" @selected(request('estado') == 'en_pausa')>En pausa</option>
                    <option value="finalizado" @selected(request('estado') == 'finalizado')>Finalizado</option>
                    <option value="cancelado" @selected(request('estado') == 'cancelado')>Cancelado</option>
                </select>
            </div>
            <div>
                <label for="cliente" class="block text-sm font-medium text-slate-700">Cliente</label>
                <select id="cliente" name="cliente" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                    <option value="">Todos</option>
                    @foreach($clientes ?? [] as $c)
                        <option value="{{ $c->id }}" @selected(request('cliente') == $c->id)>{{ $c->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                    Filtrar
                </button>
                <a href="{{ route('proyectos.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">Limpiar</a>
            </div>
        </form>
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
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Sublinea</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Monto</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Avance</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Líder</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($proyectos ?? [] as $proyecto)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900">{{ $proyecto->cp }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-900">{{ $proyecto->nombre }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $proyecto->cliente->nombre ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $proyecto->sublinea->nombre ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <x-badge :label="$proyecto->estado_label" :variant="match($proyecto->estado) {
                                    'en_ejecucion' => 'success',
                                    'en_pausa' => 'warning',
                                    'finalizado' => 'neutral',
                                    'cancelado' => 'danger',
                                    default => 'neutral',
                                }" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-slate-900">$ {{ number_format($proyecto->monto, 2) }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-24 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-full rounded-full bg-gpt-600 transition-all" style="width: {{ $proyecto->avance ?? 0 }}%"></div>
                                    </div>
                                    <span class="text-xs text-slate-500">{{ $proyecto->avance ?? 0 }}%</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $proyecto->lider->name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('proyectos.show', $proyecto) }}" class="text-sm font-medium text-gpt-600 hover:text-gpt-700 transition-colors">
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
                                    icon="briefcase"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(($proyectos ?? null) && method_exists($proyectos, 'links'))
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $proyectos->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
