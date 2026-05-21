<div>
    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl sm:text-2xl font-medium text-slate-900">Oportunidades</h2>
                <p class="mt-1 text-sm text-slate-500">Pipeline de nuevos proyectos y cotizaciones</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('oportunidades.exportar', array_filter(['anio' => $anio, 'search' => $search])) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 shadow-sm hover:bg-emerald-100 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Exportar Excel
                </a>
                <a href="{{ route('oportunidades.importar') }}" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                    <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    Importar Excel
                </a>
                <a href="{{ route('oportunidades.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Nueva oportunidad
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Year tabs --}}
    <div class="flex items-center gap-1 overflow-x-auto border-b border-slate-200 pb-0">
        @php
            $years = range(date('Y') - 2, date('Y') + 1);
        @endphp
        @foreach($years as $y)
            <button wire:click="$set('anio', '{{ $y }}')" wire:key="year-{{ $y }}"
                    class="relative whitespace-nowrap px-4 py-2.5 text-sm font-medium transition-colors {{ $anio == $y ? 'text-gpt-600' : 'text-slate-500 hover:text-slate-700' }}">
                {{ $y }}
                @if($anio == $y)
                    <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-gpt-600"></span>
                @endif
            </button>
        @endforeach
        <button wire:click="$set('anio', 'todos')"
                class="relative whitespace-nowrap px-4 py-2.5 text-sm font-medium transition-colors {{ $anio === 'todos' ? 'text-gpt-600' : 'text-slate-500 hover:text-slate-700' }}">
            Todos
            @if($anio === 'todos')
                <span class="absolute bottom-0 left-0 right-0 h-0.5 bg-gpt-600"></span>
            @endif
        </button>
    </div>

{{-- KPI cards --}}
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card
            title="Total Ofertas"
            :value="'$ ' . number_format($kpis['pipeline_monto'], 0, '.', ',')"
            :subtitle="number_format($kpis['pipeline_count']) . ' oportunidades'"
            color="blue"
        />
        <x-stat-card
            title="Monto Ponderado"
            :value="'$ ' . number_format($kpis['monto_ponderado'], 0, '.', ',')"
            :subtitle="'% adjud. promedio: ' . $kpis['ponderacion_media'] . '%'"
            color="violet"
        />
        <x-stat-card
            title="Adjudicado"
            :value="'$ ' . number_format($kpis['adjudicado_monto'], 0, '.', ',')"
            :subtitle="number_format($kpis['adjudicado_count']) . ' proyectos'"
            color="green"
        />
        <x-stat-card
            title="Enviadas"
            :value="number_format($kpis['enviadas_count'])"
            :subtitle="'de ' . number_format($kpis['pipeline_count']) . ' en pipeline'"
            color="amber"
        />
    </div>

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
    <div class="mt-6 rounded-lg border border-slate-200 bg-white overflow-hidden"
         x-data="{
             init() {
                 const top = this.$refs.topBar;
                 const body = this.$refs.tableWrap;
                 top.addEventListener('scroll', () => { body.scrollLeft = top.scrollLeft; });
                 body.addEventListener('scroll', () => { top.scrollLeft = body.scrollLeft; });
             }
         }">
        {{-- Top scrollbar mirror (synced with table) --}}
        <div x-ref="topBar" class="overflow-x-scroll border-b border-slate-100" style="height:10px">
            <div style="min-width:1812px;height:1px"></div>
        </div>
        <div x-ref="tableWrap" class="overflow-x-auto">
            {{-- ── width budget: 90+160+110+210+170+100+100+120+140+120+100+110+150+90+70+120+52 = 1812px ── --}}
            <table class="w-full table-fixed divide-y divide-slate-200" style="min-width:1812px">
                <colgroup>
                    <col style="width:90px">   {{-- CP --}}
                    <col style="width:160px">  {{-- Cliente --}}
                    <col style="width:110px">  {{-- Lugar --}}
                    <col style="width:210px">  {{-- Alcance --}}
                    <col style="width:170px">  {{-- Oferta --}}
                    <col style="width:100px">  {{-- Fecha Envío --}}
                    <col style="width:100px">  {{-- Fecha Modif. --}}
                    <col style="width:120px">  {{-- Ofertas Emitidas --}}
                    <col style="width:140px">  {{-- Hitos de Pago --}}
                    <col style="width:120px">  {{-- Responsable --}}
                    <col style="width:100px">  {{-- Status --}}
                    <col style="width:110px">  {{-- Arch. Oferta --}}
                    <col style="width:150px">  {{-- Concepto Adj. --}}
                    <col style="width:90px">   {{-- % Adj. --}}
                    <col style="width:70px">   {{-- % Real --}}
                    <col style="width:120px">  {{-- Cartera Esp. --}}
                    <col style="width:52px">   {{-- Actions --}}
                </colgroup>
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sticky left-0 z-20 bg-slate-50">CP</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sticky left-[90px] z-20 bg-slate-50">Cliente</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sticky left-[250px] z-20 bg-slate-50 border-r border-slate-300">Lugar</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Alcance</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Oferta</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fecha Envío</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fecha Modif.</th>
                        <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Ofertas Emitidas</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Hitos de Pago</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Responsable</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Arch. Oferta</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Resultado.</th>
                        <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">% Adj.</th>
                        <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">% Real</th>
                        <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Cartera Esp.</th>
                        <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500"></th>
                    </tr>
                </thead>

                @forelse($oportunidades as $op)
                    @php
                        $fechaEnvio = $op->fecha_envio
                            ?? $op->cotizaciones->where('fecha_emision', '!=', null)->sortByDesc('version')->first()?->fecha_emision;
                        $fechaModif = $op->fecha_modificacion_oferta;
                        $ponderacionLabel = match((int) $op->ponderacion) {
                            10 => 'Remoto',
                            25 => 'Posible',
                            50 => 'Probable',
                            75 => 'Casi Probable',
                            100 => 'Contratado',
                            default => $op->ponderacion . '%',
                        };
                        $incompleto = ! $op->cliente_id || ! $fechaEnvio;
                        $clienteNombre = $op->cliente?->razon_social ?? '—';
                        $responsableNombre = $op->elaboro?->name ?? $op->gerenteProyectos?->name ?? '—';
                    @endphp

                    {{-- Each row pair wrapped in its own tbody for Alpine expand state --}}
                    <tbody x-data="{ expanded: false }" class="divide-y divide-slate-100">

                        {{-- ── Main data row ──────────────────────────────────── --}}
                        <tr class="bg-white hover:bg-slate-50/60 transition-colors cursor-pointer select-none group {{ $incompleto ? 'border-l-2 border-l-amber-400' : '' }}"
                            @click="expanded = !expanded">

                            {{-- CP + chevron toggle --}}
                            <td class="px-3 py-3 text-sm font-mono font-medium text-slate-900 sticky left-0 z-10 bg-white group-hover:bg-slate-50/60 overflow-hidden">
                                <div class="flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-slate-400 transition-transform duration-200"
                                         :class="{ 'rotate-90': expanded }"
                                         fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                                    </svg>
                                    <a href="{{ route('oportunidades.show', $op) }}"
                                       class="hover:text-gpt-600 transition-colors truncate"
                                       @click.stop>{{ $op->cp_numero ?? '—' }}</a>
                                </div>
                            </td>

                            {{-- Cliente --}}
                            <td class="px-3 py-3 text-sm text-slate-900 sticky left-[90px] z-10 bg-white group-hover:bg-slate-50/60 overflow-hidden">
                                @if($op->cliente_id)
                                    <div class="truncate">{{ $clienteNombre }}</div>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">
                                        <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                                        Sin cliente
                                    </span>
                                @endif
                            </td>
                            {{-- Lugar --}}
                            <td class="px-3 py-3 text-sm text-slate-600 sticky left-[250px] z-10 bg-white group-hover:bg-slate-50/60 border-r border-slate-200 overflow-hidden">
                                <div class="truncate">{{ $op->lugar?->nombre ?? '—' }}</div>
                            </td>

                            {{-- Alcance --}}
                            <td class="px-3 py-3 text-sm text-slate-600 overflow-hidden">
                                <div class="truncate">{{ $op->alcance ?? '—' }}</div>
                            </td>

                            {{-- Oferta (tech_reference) --}}
                            <td class="px-3 py-3 text-sm text-slate-600 overflow-hidden">
                                <div class="truncate">{{ $op->tech_reference ?? '—' }}</div>
                            </td>

                            {{-- Fecha Envío --}}
                            <td class="px-3 py-3 text-sm overflow-hidden {{ $fechaEnvio ? 'text-slate-600' : 'text-amber-500' }}">
                                <div class="truncate">{{ $fechaEnvio?->format('Y-m-d') ?? '—' }}</div>
                            </td>

                            {{-- Fecha Modif. --}}
                            <td class="px-3 py-3 text-sm text-slate-600 overflow-hidden">
                                <div class="truncate">{{ $fechaModif?->format('Y-m-d') ?? '—' }}</div>
                            </td>

                            {{-- Ofertas Emitidas --}}
                            <td class="px-3 py-3 text-right text-sm font-medium overflow-hidden {{ $op->monto_usd ? 'text-slate-900' : 'text-slate-400' }}">
                                <div class="truncate">{{ $op->monto_usd ? '$ ' . number_format($op->monto_usd, 0, '.', ',') : '—' }}</div>
                            </td>

                            {{-- Hitos de Pago --}}
                            <td class="px-3 py-3 text-sm text-slate-600 overflow-hidden">
                                <div class="truncate">{{ $op->hitos_pago ?? '—' }}</div>
                            </td>

                            {{-- Responsable --}}
                            <td class="px-3 py-3 text-sm text-slate-700 overflow-hidden">
                                <div class="truncate">{{ $responsableNombre }}</div>
                            </td>

                            {{-- Status --}}
                            <td class="px-3 py-3 overflow-hidden">
                                <x-badge :status="$op->estado" />
                            </td>

                            {{-- Arch. Oferta --}}
                            <td class="px-3 py-3 text-sm overflow-hidden">
                                <div class="truncate">
                                    @if($op->archivo_oferta)
                                        <a href="{{ Storage::url($op->archivo_oferta) }}" target="_blank"
                                           class="text-gpt-600 hover:underline text-xs"
                                           @click.stop>{{ basename($op->archivo_oferta) }}</a>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Concepto Adj. --}}
                            <td class="px-3 py-3 text-sm text-slate-600 overflow-hidden">
                                <div class="truncate">{{ $op->concepto_adjudicacion ?? '—' }}</div>
                            </td>

                            {{-- % Adj. (ponderacion) --}}
                            <td class="px-3 py-3 text-right overflow-hidden">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    {{ $op->ponderacion >= 75 ? 'bg-green-100 text-green-800' : ($op->ponderacion >= 50 ? 'bg-amber-100 text-amber-800' : ($op->ponderacion >= 25 ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700')) }}">
                                    {{ $op->ponderacion }}%
                                </span>
                                <span class="block text-[10px] text-slate-400 text-center">{{ $ponderacionLabel }}</span>
                            </td>

                            {{-- % Real --}}
                            <td class="px-3 py-3 text-right text-sm text-slate-700 overflow-hidden">
                                <div class="truncate">{{ $op->porcentaje_adjudicacion ? number_format($op->porcentaje_adjudicacion, 1) . '%' : '—' }}</div>
                            </td>

                            {{-- Cartera Esperada --}}
                            <td class="px-3 py-3 text-right text-sm font-medium overflow-hidden {{ $op->cartera_esperada ? 'text-slate-900' : 'text-slate-400' }}">
                                <div class="truncate">{{ $op->cartera_esperada ? '$ ' . number_format($op->cartera_esperada, 0, '.', ',') : '—' }}</div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-3 py-3 text-right relative" x-data="{ open: false }" @click.stop @click.outside="open = false">
                                <button @click="open = !open"
                                        class="inline-flex items-center rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
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
                                        <form method="POST" action="{{ route('oportunidades.cambiar-estado', $op) }}" class="inline"
                                              onsubmit="return confirm('¿Cancelar esta oportunidad?')">
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

                        {{-- ── Expandable detail panel ─────────────────────────── --}}
                        <tr x-show="expanded" x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1">
                            <td colspan="19" class="bg-slate-50/80 px-6 py-4 border-b border-slate-200">

                                {{-- Header bar --}}
                                <div class="mb-3 flex items-center justify-between">
                                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Detalle — {{ $op->cp_numero ?? 'sin CP' }}</span>
                                    <button @click="expanded = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>

                                <div class="grid grid-cols-1 gap-x-8 gap-y-3 sm:grid-cols-3 lg:grid-cols-4">

                                    {{-- Cliente --}}
                                    <div>
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Cliente</dt>
                                        <dd class="mt-0.5 text-sm text-slate-800 break-words">
                                            @if($op->cliente_id) {{ $clienteNombre }}
                                            @else <span class="text-amber-600 font-medium">Sin cliente registrado</span>
                                            @endif
                                        </dd>
                                    </div>

                                    {{-- Contacto --}}
                                    <div>
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Contacto</dt>
                                        <dd class="mt-0.5 text-sm text-slate-800 break-words">{{ $op->contacto ?? '—' }}</dd>
                                    </div>

                                    {{-- Datos de Contacto --}}
                                    <div class="col-span-2">
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Datos de Contacto</dt>
                                        <dd class="mt-0.5 text-sm text-slate-800 break-words whitespace-pre-wrap">{{ $op->datos_contacto ?? '—' }}</dd>
                                    </div>

                                    {{-- Lugar --}}
                                    <div>
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Lugar</dt>
                                        <dd class="mt-0.5 text-sm text-slate-800">{{ $op->lugar?->nombre ?? '—' }}</dd>
                                    </div>

                                    {{-- Alcance (full, spans 3 cols) --}}
                                    <div class="col-span-3">
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Alcance</dt>
                                        <dd class="mt-0.5 text-sm text-slate-800 break-words whitespace-pre-wrap">{{ $op->alcance ?? '—' }}</dd>
                                    </div>

                                    {{-- Oferta --}}
                                    <div class="col-span-2">
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Oferta / Tech. Ref.</dt>
                                        <dd class="mt-0.5 text-sm text-slate-800 break-words">{{ $op->tech_reference ?? '—' }}</dd>
                                    </div>

                                    {{-- Fecha Envío --}}
                                    <div>
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Fecha Envío</dt>
                                        <dd class="mt-0.5 text-sm {{ $fechaEnvio ? 'text-slate-800' : 'text-amber-600 font-medium' }}">
                                            {{ $fechaEnvio?->format('d/m/Y') ?? 'Sin fecha' }}
                                        </dd>
                                    </div>

                                    {{-- Fecha Modif. --}}
                                    <div>
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Fecha Modif. Oferta</dt>
                                        <dd class="mt-0.5 text-sm text-slate-800">{{ $fechaModif?->format('d/m/Y') ?? '—' }}</dd>
                                    </div>

                                    {{-- Ofertas Emitidas --}}
                                    <div>
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Ofertas Emitidas</dt>
                                        <dd class="mt-0.5 text-sm font-semibold text-slate-900">
                                            {{ $op->monto_usd ? '$ ' . number_format($op->monto_usd, 2, '.', ',') : '—' }}
                                        </dd>
                                    </div>

                                    {{-- Hitos de Pago (full) --}}
                                    <div class="col-span-3">
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Hitos de Pago</dt>
                                        <dd class="mt-0.5 text-sm text-slate-800 break-words whitespace-pre-wrap">{{ $op->hitos_pago ?? '—' }}</dd>
                                    </div>

                                    {{-- Responsable --}}
                                    <div>
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Responsable</dt>
                                        <dd class="mt-0.5 text-sm text-slate-800">{{ $responsableNombre }}</dd>
                                    </div>

                                    {{-- Status --}}
                                    <div>
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Status</dt>
                                        <dd class="mt-0.5"><x-badge :status="$op->estado" /></dd>
                                    </div>

                                    {{-- Arch. Oferta --}}
                                    <div>
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Arch. Oferta</dt>
                                        <dd class="mt-0.5 text-sm">
                                            @if($op->archivo_oferta)
                                                <a href="{{ Storage::url($op->archivo_oferta) }}" target="_blank"
                                                   class="inline-flex items-center gap-1 text-gpt-600 hover:underline break-all">
                                                    <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                                                    {{ basename($op->archivo_oferta) }}
                                                </a>
                                            @else
                                                <span class="text-slate-400">—</span>
                                            @endif
                                        </dd>
                                    </div>

                                    {{-- Concepto Adj. (full) --}}
                                    <div class="col-span-2">
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Concepto de Adjudicación</dt>
                                        <dd class="mt-0.5 text-sm text-slate-800 break-words whitespace-pre-wrap">{{ $op->concepto_adjudicacion ?? '—' }}</dd>
                                    </div>

                                    {{-- % Adj. --}}
                                    <div>
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">% Adjudicación</dt>
                                        <dd class="mt-1">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                                {{ $op->ponderacion >= 75 ? 'bg-green-100 text-green-800' : ($op->ponderacion >= 50 ? 'bg-amber-100 text-amber-800' : ($op->ponderacion >= 25 ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700')) }}">
                                                {{ $op->ponderacion }}% — {{ $ponderacionLabel }}
                                            </span>
                                        </dd>
                                    </div>

                                    {{-- % Real --}}
                                    <div>
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">% Real</dt>
                                        <dd class="mt-0.5 text-sm font-semibold text-slate-900">
                                            {{ $op->porcentaje_adjudicacion ? number_format($op->porcentaje_adjudicacion, 1) . '%' : '—' }}
                                        </dd>
                                    </div>

                                    {{-- Cartera Esperada --}}
                                    <div>
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Cartera Esperada</dt>
                                        <dd class="mt-0.5 text-sm font-semibold text-slate-900">
                                            {{ $op->cartera_esperada ? '$ ' . number_format($op->cartera_esperada, 2, '.', ',') : '—' }}
                                        </dd>
                                    </div>

                                </div>
                            </td>
                        </tr>

                    </tbody>
                @empty
                <tbody>
                    <tr>
                        <td colspan="19" class="px-4 py-12 text-center">
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
                    </tbody>
                    @endforelse
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