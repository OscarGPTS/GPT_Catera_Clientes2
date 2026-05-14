<x-layouts.app>
    @section('title', 'Mi Asignación')

    <div class="mb-6">
        <h2 class="text-2xl font-medium text-slate-900">Mi Asignación</h2>
        <p class="mt-1 text-sm text-slate-500">Proyectos y carga asignada actualmente</p>
    </div>

    @php
        $proyectosAsignados = auth()->user()->proyectosComoIngeniero ?? collect();
        $proyectosComoGerente = auth()->user()->proyectosComoGerente ?? collect();
        $todosProyectos = $proyectosAsignados->merge($proyectosComoGerente);
        $totalProyectos = $todosProyectos->count();
        $cargaActual = $totalProyectos;
    @endphp

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-stat-card
            title="Carga actual"
            value="{{ $cargaActual }} proyectos"
            subtitle="CP + DN activos"
            :color="$cargaActual > 8 ? 'red' : ($cargaActual > 4 ? 'amber' : 'green')"
        />
        <x-stat-card
            title="Proyectos asignados"
            value="{{ $totalProyectos }}"
            subtitle="Total activos"
            color="blue"
        />
        <x-stat-card
            title="Proyectos en ejecución"
            value="{{ $todosProyectos->where('estado', 'en_ejecucion')->count() }}"
            subtitle="En obra actualmente"
            color="gpt"
        />
    </div>

    <div class="mt-6 rounded-lg border border-slate-200 bg-white overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
            <h3 class="text-base font-medium text-slate-900">Proyectos asignados</h3>
        </div>

        @if($todosProyectos->isNotEmpty())
            <div class="divide-y divide-slate-100">
                @foreach($todosProyectos as $proyecto)
                    <div class="flex items-center justify-between px-6 py-4 hover:bg-slate-50">
                        <div class="flex items-center gap-4">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gpt-100 text-sm font-medium text-gpt-700">
                                {{ $proyecto->sublinea?->codigo ?? '—' }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">
                                    {{ $proyecto->cp_numero ?? $proyecto->dn_numero ?? '—' }}
                                </p>
                                <p class="text-xs text-slate-500">
                                    {{ $proyecto->cliente?->razon_social ?? 'Sin cliente' }}
                                    @if($proyecto->cliente?->alias)
                                        ({{ $proyecto->cliente->alias }})
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <x-badge :status="$proyecto->estado" :label="$proyecto->estado" />
                            <a href="#" class="text-xs font-medium text-gpt-600 hover:text-gpt-700">Ver</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                <h3 class="mt-4 text-lg font-medium text-slate-900">Sin proyectos asignados</h3>
                <p class="mt-1 text-sm text-slate-500">Actualmente no tienes ningún proyecto asignado. Tu carga se mostrará aquí cuando se te asigne un proyecto.</p>
            </div>
        @endif
    </div>
</x-layouts.app>
