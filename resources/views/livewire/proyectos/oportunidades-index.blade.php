<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-medium text-slate-900">Oportunidades</h2>
                <p class="mt-1 text-sm text-slate-500">Pipeline de nuevos proyectos y cotizaciones</p>
            </div>
            <a href="{{ route('oportunidades.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Nueva oportunidad
            </a>
        </div>
    </x-slot>

    {{-- Year tabs --}}
    <div class="flex items-center gap-1 border-b border-slate-200 pb-0">
        @php
            $years = range(date('Y') - 2, date('Y') + 1);
        @endphp
        @foreach($years as $y)
            <button wire:click="$set('anio', '{{ $y }}')" wire:key="year-{{ $y }}"
                    class="relative px-4 py-2.5 text-sm font-medium transition-colors {{ $anio == $y ? 'text-gpt-600' : 'text-slate-500 hover:text-slate-700' }}">
                {{ $y }}
                @if($anio == $y)
                    <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-gpt-600"></span>
                @endif
            </button>
        @endforeach
        <button wire:click="$set('anio', 'todos')"
                class="relative px-4 py-2.5 text-sm font-medium transition-colors {{ $anio === 'todos' ? 'text-gpt-600' : 'text-slate-500 hover:text-slate-700' }}">
            Todos
            @if($anio === 'todos')
                <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-gpt-600"></span>
            @endif
        </button>
    </div>

    {{-- KPI cards --}}
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card
            title="Pipeline total"
            :value="'$ ' . number_format($kpis['pipeline_monto'], 2, '.', ',')"
            :subtitle="number_format($kpis['pipeline_count']) . ' oportunidades activas'"
            color="blue"
        />
        <x-stat-card
            title="Adjudicado"
            :value="'$ ' . number_format($kpis['adjudicado_monto'], 2, '.', ',')"
            :subtitle="number_format($kpis['adjudicado_count']) . ' proyectos'"
            color="green"
        />
        <x-stat-card
            title="Hit rate"
            :value="$kpis['hit_rate_conteo'] . '% / ' . $kpis['hit_rate_monto'] . '%'"
            subtitle="Conteo / Monto"
            color="amber"
        />
        <x-stat-card
            title="Concentración SEDENA"
            :value="$kpis['sedena_concentracion'] . '%'"
            subtitle="Del pipeline total"
            :color="$kpis['sedena_concentracion'] > 50 ? 'red' : 'amber'"
        />
    </div>

    {{-- SEDENA Alert --}}
    @if($kpis['sedena_concentracion'] > 50)
        <div class="mt-4 rounded-lg border border-gpt-red-200 bg-gpt-red-50 p-4">
            <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gpt-red-100">
                    <svg class="h-5 w-5 text-gpt-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gpt-red-800">
                        Concentración SEDENA {{ $kpis['sedena_concentracion'] }}% del pipeline — Supera el umbral de riesgo del 50%
                    </p>
                    <p class="mt-1 text-xs text-gpt-red-600">Se recomienda diversificar la cartera de clientes para mitigar riesgos operativos y financieros.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <div class="mt-6 rounded-lg border border-slate-200 bg-white p-4">
        <div class="flex flex-wrap items-center gap-3">
            {{-- Search --}}
            <div class="relative flex-1 min-w-[240px]">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar por Tech Reference, cliente, CP..."
                       class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
            </div>

            {{-- Sublinea multi-select --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    Sublínea
                    @if(count($sublineasSeleccionadas) > 0)
                        <span class="text-xs text-gpt-600">({{ count($sublineasSeleccionadas) }})</span>
                    @endif
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-cloak x-transition
                     class="absolute left-0 z-30 mt-1 w-56 rounded-lg border border-slate-200 bg-white shadow-lg">
                    <div class="p-2 space-y-0.5 max-h-64 overflow-y-auto">
                        @foreach($sublineas as $sub)
                            <label class="flex items-center gap-2 rounded-md px-2 py-1.5 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" value="{{ $sub->id }}" wire:model.live="sublineasSeleccionadas"
                                       class="h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                                <span class="text-sm text-slate-700">{{ $sub->codigo }} — {{ $sub->nombre }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if(count($sublineasSeleccionadas) > 0)
                        <div class="border-t border-slate-100 px-2 py-1.5">
                            <button wire:click="$set('sublineasSeleccionadas', [])" class="text-xs text-slate-500 hover:text-gpt-600 transition-colors">Limpiar filtro</button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Estado multi-select --}}
            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                <button type="button" @click="open = !open"
                        class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                    Estado
                    @if(count($estadosSeleccionados) > 0)
                        <span class="text-xs text-gpt-600">({{ count($estadosSeleccionados) }})</span>
                    @endif
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-cloak x-transition
                     class="absolute right-0 z-30 mt-1 w-60 rounded-lg border border-slate-200 bg-white shadow-lg">
                    <div class="p-2 space-y-0.5 max-h-72 overflow-y-auto">
                        @foreach($estados as $key => $label)
                            <label class="flex items-center gap-2 rounded-md px-2 py-1.5 hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" value="{{ $key }}" wire:model.live="estadosSeleccionados"
                                       class="h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                                <span class="text-sm text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if(count($estadosSeleccionados) > 0)
                        <div class="border-t border-slate-100 px-2 py-1.5">
                            <button wire:click="$set('estadosSeleccionados', [])" class="text-xs text-slate-500 hover:text-gpt-600 transition-colors">Limpiar filtro</button>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Solo mis oportunidades --}}
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model.live="soloMios" class="sr-only peer">
                <div class="relative h-5 w-9 rounded-full bg-slate-200 peer-checked:bg-gpt-600 transition-colors after:absolute after:start-0.5 after:top-0.5 after:h-4 after:w-4 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-4"></div>
                <span class="text-sm text-slate-700 whitespace-nowrap">Solo mis oportunidades</span>
            </label>

            {{-- Clear --}}
            <button wire:click="clearFilters" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">Limpiar</button>
        </div>
    </div>

    {{-- Data table --}}
    <div class="mt-6 rounded-lg border border-slate-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">CP</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tech Reference</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Cliente</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Sublínea</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Sector</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Monto estimado</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Líder</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Última actividad</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($oportunidades as $op)
                        @php
                            $sublineaColors = [
                                'HTP' => 'bg-gpt-100 text-gpt-800',
                                'LSP' => 'bg-blue-100 text-blue-800',
                                'VLV' => 'bg-green-100 text-green-800',
                                'SOL' => 'bg-amber-100 text-amber-800',
                                'SG'  => 'bg-slate-100 text-slate-700',
                            ];
                            $subKey = $op->sublinea->codigo ?? '';
                            $subColor = $sublineaColors[$subKey] ?? 'bg-slate-100 text-slate-700';
                            $clienteAlias = $op->cliente->alias_3letras ?? mb_strtoupper(mb_substr($op->cliente->razon_social ?? 'N/A', 0, 3));
                            $liderInitials = collect(explode(' ', $op->gerenteProyectos->name ?? 'N A'))->map(fn($n) => mb_strtoupper(mb_substr($n, 0, 1)))->take(2)->implode('');
                            $monto = $op->cotizaciones->where('status', 'aprobado')->sortByDesc('version')->first()?->precio_venta_final
                                ?? $op->cotizaciones->sortByDesc('version')->first()?->precio_venta_final
                                ?? 0;
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-mono font-medium text-slate-900">
                                <a href="{{ route('oportunidades.show', $op) }}" class="hover:text-gpt-600 transition-colors">{{ $op->cp_numero ?? '—' }}</a>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="text-sm text-slate-600 block max-w-[180px] truncate" title="{{ $op->tech_reference }}">
                                    {{ $op->tech_reference ?? '—' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center rounded-md bg-slate-100 px-1.5 py-0.5 text-xs font-bold text-slate-600 uppercase min-w-[28px]">
                                        {{ $clienteAlias }}
                                    </span>
                                    <span class="text-sm text-slate-900">{{ $op->cliente->razon_social ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if($subKey)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $subColor }}">
                                        {{ $subKey }}
                                    </span>
                                @else
                                    <span class="text-sm text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $op->sector ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-slate-900">
                                @if($monto > 0)
                                    $ {{ number_format($monto, 0, '.', ',') }}
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <x-badge :status="$op->estado" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gpt-100 text-xs font-semibold text-gpt-700">
                                        {{ $liderInitials }}
                                    </span>
                                    <span class="text-sm text-slate-700">{{ $op->gerenteProyectos->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500">{{ $op->updated_at?->diffForHumans() ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right" x-data="{ open: false }" @click.outside="open = false">
                                <button @click="open = !open" class="inline-flex items-center rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z"/>
                                    </svg>
                                </button>
                                <div x-show="open" x-cloak x-transition
                                     class="absolute right-0 z-20 mt-1 w-44 rounded-lg border border-slate-200 bg-white shadow-lg">
                                    <div class="py-1">
                                        <a href="{{ route('oportunidades.show', $op) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Ver
                                        </a>
                                        <a href="{{ route('proyectos.cotizacion', $op) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Cotizar
                                        </a>
                                        @can('adjudicar proyecto')
                                            <a href="{{ route('proyectos.adjudicar', $op) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors">
                                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                                                Adjudicar
                                            </a>
                                        @endcan
                                        <hr class="my-1 border-slate-100">
                                        <form method="POST" action="{{ route('oportunidades.cambiar-estado', $op) }}" class="inline" onsubmit="return confirm('¿Cancelar esta oportunidad?')">
                                            @csrf
                                            <input type="hidden" name="estado" value="cancelado">
                                            <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gpt-red-600 hover:bg-gpt-red-50 transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                Cancelar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                    <h3 class="mt-2 text-sm font-medium text-slate-900">Sin oportunidades</h3>
                                    <p class="mt-1 text-sm text-slate-500">No se encontraron oportunidades con los filtros seleccionados.</p>
                                    <a href="{{ route('oportunidades.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                        Nueva oportunidad
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination footer --}}
        @if($oportunidades->hasPages())
            <div class="flex flex-wrap items-center justify-between gap-4 border-t border-slate-200 px-4 py-3">
                <div class="text-sm text-slate-500">
                    Mostrando {{ $oportunidades->firstItem() ?? 0 }}&ndash;{{ $oportunidades->lastItem() ?? 0 }} de {{ number_format($oportunidades->total()) }} oportunidades
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-slate-500">Mostrar</label>
                        <select wire:model.live="perPage" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-sm text-slate-700 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    {{ $oportunidades->links() }}
                </div>
            </div>
        @endif
    </div>
</div>