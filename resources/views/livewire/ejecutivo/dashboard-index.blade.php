<script>window._dashData = @json($chartData);</script>
<div>
    <x-slot name="header">
        @php
            $hora = (int) date('H');
            $saludo = match(true) {
                $hora < 12 => 'Buenos días',
                $hora < 18 => 'Buenas tardes',
                default => 'Buenas noches',
            };
        @endphp
        <div>
            <h2 class="text-2xl font-medium text-slate-900">{{ $saludo }}, {{ $nombre_usuario }}</h2>
            <p class="mt-1 text-sm text-slate-500">Vista ejecutiva &middot; {{ $quarter_label }} &middot; Datos al {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </x-slot>

    {{-- Global filters row --}}
    <div class="flex flex-wrap items-center gap-3">
        {{-- Período selector --}}
        <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5" role="group">
            @foreach(['mes' => 'Mes', 'trimestre' => 'Trimestre', 'anio' => 'Año', 'custom' => 'Custom'] as $key => $label)
                <button wire:click="$set('periodo', '{{ $key }}')"
                        class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors {{ $periodo == $key ? 'bg-gpt-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Cliente multi-select --}}
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Cliente
                @if(count($clientesSeleccionados) > 0)
                    <span class="text-xs text-gpt-600">({{ count($clientesSeleccionados) }})</span>
                @endif
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-cloak class="absolute left-0 z-30 mt-1 w-64 rounded-lg border border-slate-200 bg-white shadow-lg">
                <div class="p-2">
                    <input type="text" placeholder="Buscar cliente..." class="w-full rounded-md border border-slate-200 px-3 py-1.5 text-sm focus:border-gpt-600 focus:ring-gpt-600 mb-2">
                </div>
                <div class="max-h-48 overflow-y-auto px-2 pb-2 space-y-0.5">
                    @foreach($clientes as $cliente)
                        <label class="flex items-center gap-2 rounded-md px-2 py-1.5 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" value="{{ $cliente->id }}" wire:model="clientesSeleccionados"
                                   class="h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                            <span class="text-sm text-slate-700">{{ $cliente->razon_social }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="border-t border-slate-100 px-2 py-1.5">
                    <button type="button" wire:click="$set('clientesSeleccionados', [])" class="text-xs text-slate-500 hover:text-gpt-600">Limpiar</button>
                </div>
            </div>
        </div>

        {{-- Sublínea multi-select --}}
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
            <div x-show="open" x-cloak class="absolute left-0 z-30 mt-1 w-56 rounded-lg border border-slate-200 bg-white shadow-lg">
                <div class="p-2 space-y-0.5">
                    @foreach($sublineas as $sub)
                        <label class="flex items-center gap-2 rounded-md px-2 py-1.5 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" value="{{ $sub->id }}" wire:model="sublineasSeleccionadas"
                                   class="h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                            <span class="text-sm text-slate-700">{{ $sub->nombre }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="border-t border-slate-100 px-2 py-1.5">
                    <button type="button" wire:click="$set('sublineasSeleccionadas', [])" class="text-xs text-slate-500 hover:text-gpt-600">Limpiar</button>
                </div>
            </div>
        </div>

        {{-- Descargar reporte mensual PDF (placeholder) --}}
        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm hover:bg-slate-50 transition-colors ml-auto cursor-not-allowed opacity-60" disabled title="Próximamente">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Reporte PDF — Próximamente
        </button>
    </div>

    {{-- ============================================================ --}}
    {{-- SECCIÓN PRINCIPAL: Cartera por mes y probabilidad (Chart.js) --}}
    {{-- ============================================================ --}}
    <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="grid grid-cols-1 xl:grid-cols-5 divide-y xl:divide-y-0 xl:divide-x divide-slate-100">

            {{-- Chart A (3/5): Stacked bar — cartera por mes × probabilidad --}}
            <div class="xl:col-span-3 p-6">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Cartera por mes y probabilidad de adjudicación</h3>
                        <p class="mt-0.5 text-sm text-slate-500">Monto USD (millones) apilado por nivel · Nov 2025 – Dic 2026 · todos los proyectos</p>
                    </div>
                    <div class="flex flex-wrap gap-x-3 gap-y-1 justify-end text-[11px] text-slate-500 shrink-0">
                        <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-sm bg-emerald-500"></span>Contratada</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-sm bg-cyan-500"></span>75%</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-sm bg-blue-500"></span>50%</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-sm bg-amber-500"></span>25%</span>
                        <span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded-sm bg-slate-400"></span>Remoto</span>
                    </div>
                </div>
                <div class="relative h-64 w-full"
                     x-data
                     x-init="
                        const d = window._dashData;
                        const ctx = $el.querySelector('canvas').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: d.monthLabels,
                                datasets: [
                                    { label: 'Contratada (100%)', data: d.byMonth.p100, backgroundColor: 'rgba(16,185,129,0.8)', stack: 's', borderRadius: 3 },
                                    { label: 'Casi Probable (75%)', data: d.byMonth.p75, backgroundColor: 'rgba(6,182,212,0.8)', stack: 's', borderRadius: 3 },
                                    { label: 'Probable (50%)', data: d.byMonth.p50, backgroundColor: 'rgba(59,130,246,0.8)', stack: 's', borderRadius: 3 },
                                    { label: 'Posible (25%)', data: d.byMonth.p25, backgroundColor: 'rgba(245,158,11,0.8)', stack: 's', borderRadius: 3 },
                                    { label: 'Remoto (10%)', data: d.byMonth.p10, backgroundColor: 'rgba(148,163,184,0.6)', stack: 's', borderRadius: 3 },
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: { mode: 'index', intersect: false },
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: { label: function(c) { return ' ' + c.dataset.label + ': $' + c.parsed.y.toFixed(2) + 'M'; } }
                                    }
                                },
                                scales: {
                                    x: { stacked: true, grid: { color: 'rgba(148,163,184,0.15)' }, ticks: { font: { size: 11 }, color: '#94a3b8' } },
                                    y: { stacked: true, grid: { color: 'rgba(148,163,184,0.15)' }, ticks: { font: { size: 11 }, color: '#94a3b8', callback: function(v) { return '$' + v + 'M'; } } }
                                }
                            }
                        });
                     ">
                    <canvas></canvas>
                </div>
            </div>

            {{-- Chart B (2/5): Horizontal — sublínea + cliente --}}
            <div class="xl:col-span-2 grid grid-rows-2 divide-y divide-slate-100">

                {{-- B1: Por sublínea --}}
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-slate-900 mb-0.5">Pipeline por sublínea</h3>
                    <p class="text-xs text-slate-500 mb-3">Monto total USD por línea de negocio</p>
                    <div class="relative h-28 w-full"
                         x-data
                         x-init="
                            const d = window._dashData;
                            const labels = Object.keys(d.bySublinea);
                            const values = Object.values(d.bySublinea);
                            const colors = ['rgba(99,102,241,0.7)','rgba(59,130,246,0.7)','rgba(16,185,129,0.7)','rgba(245,158,11,0.7)','rgba(239,68,68,0.7)','rgba(6,182,212,0.7)','rgba(251,113,133,0.7)','rgba(100,116,139,0.7)','rgba(234,179,8,0.7)','rgba(20,184,166,0.7)'];
                            const ctx = $el.querySelector('canvas').getContext('2d');
                            new Chart(ctx, {
                                type: 'bar',
                                data: { labels, datasets: [{ label: 'USD M', data: values, backgroundColor: colors.slice(0, labels.length), borderRadius: 3 }] },
                                options: {
                                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(c){ return ' $' + c.parsed.x.toFixed(2) + 'M USD'; } } } },
                                    scales: {
                                        x: { grid: { color: 'rgba(148,163,184,0.15)' }, ticks: { font: { size: 10 }, color: '#94a3b8', callback: function(v){ return '$' + v + 'M'; } } },
                                        y: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#374151' } }
                                    }
                                }
                            });
                         ">
                        <canvas></canvas>
                    </div>
                </div>

                {{-- B2: Por cliente --}}
                <div class="p-6">
                    <h3 class="text-sm font-semibold text-slate-900 mb-0.5">Top clientes por monto</h3>
                    <p class="text-xs text-slate-500 mb-3">Concentración de cartera por cliente</p>
                    <div class="relative h-28 w-full"
                         x-data
                         x-init="
                            const d = window._dashData;
                            const labels = Object.keys(d.byCliente);
                            const values = Object.values(d.byCliente);
                            const total = values.reduce(function(a,b){ return a+b; }, 0);
                            const colors = ['rgba(239,68,68,0.7)','rgba(59,130,246,0.7)','rgba(16,185,129,0.7)','rgba(245,158,11,0.7)','rgba(139,92,246,0.7)','rgba(6,182,212,0.7)','rgba(251,113,133,0.7)','rgba(100,116,139,0.7)'];
                            const ctx = $el.querySelector('canvas').getContext('2d');
                            new Chart(ctx, {
                                type: 'bar',
                                data: { labels, datasets: [{ label: 'USD M', data: values, backgroundColor: colors.slice(0, labels.length), borderRadius: 3 }] },
                                options: {
                                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: function(c){ return ' $' + c.parsed.x.toFixed(2) + 'M (' + ((c.parsed.x / total) * 100).toFixed(0) + '%)'; } } } },
                                    scales: {
                                        x: { grid: { color: 'rgba(148,163,184,0.15)' }, ticks: { font: { size: 10 }, color: '#94a3b8', callback: function(v){ return '$' + v + 'M'; } } },
                                        y: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#374151' } }
                                    }
                                }
                            });
                         ">
                        <canvas></canvas>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Section 1: KPI cards --}}
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card
            title="Pipeline total"
            :value="'$ ' . number_format($pipelineMonto, 2, '.', ',')"
            :subtitle="$pipelineTotal . ' oportunidades'"
            color="blue"
        />
        <x-stat-card
            title="Adjudicado"
            :value="$adjudicadosCount . ' proyectos'"
            subtitle="En estados adjudicado+"
            color="green"
        />
        <x-stat-card
            title="Hit rate"
            :value="$hitRateConteo . '%'"
            :subtitle="'Conteo · ' . $pipelineTotal . ' total'"
            color="amber"
        />
        <x-stat-card
            title="Concentración SEDENA"
            :value="number_format($concentracionSedena, 1) . '%'"
            subtitle="Del pipeline total"
            :color="$concentracionSedena > 50 ? 'red' : 'amber'"
        />
    </div>

    {{-- Adjudicado progress --}}
    <div class="mt-4 rounded-lg border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-slate-700">Progreso hacia meta de adjudicación</span>
            <span class="text-sm text-slate-500">{{ min($metaPct, 100) }}% — $ {{ number_format($adjudicadoMonto, 0, '.', ',') }} de $ {{ number_format($metaAdjudicacion, 0, '.', ',') }}</span>
        </div>
        <div class="h-2.5 w-full rounded-full bg-slate-100">
            <div class="h-2.5 rounded-full bg-green-500 transition-all" style="width: {{ min($metaPct, 100) }}%"></div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SECCIÓN: Evolución de Adjudicaciones 2026 (Chart.js)         --}}
    {{-- ============================================================ --}}
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-5">

        {{-- Chart A: Evolución mensual acumulada (ocupa 3/5 del ancho) --}}
        <div class="xl:col-span-3 rounded-lg border border-slate-200 bg-white p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-4">
                <div>
                    <h3 class="text-base font-medium text-slate-900">Evolución de adjudicaciones 2026</h3>
                    <p class="mt-0.5 text-sm text-slate-500">Adjudicado real acumulado vs. cartera esperada vs. meta anual</p>
                </div>
                <div class="flex flex-wrap gap-3 text-xs">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-5 rounded-full bg-green-500 inline-block"></span>Adj. real</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-5 rounded-sm border-t-2 border-dashed border-blue-400 inline-block"></span>Cartera esperada</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-5 rounded-sm border-t-2 border-dashed border-amber-400 inline-block"></span>Meta</span>
                </div>
            </div>
            <div class="relative h-64 w-full"
                 x-data
                 x-init="
                    const ctx = $el.querySelector('canvas').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
                            datasets: [
                                {
                                    label: 'Adjudicado real (USD M)',
                                    data: [0, 0, 0, 0, 0, null, null, null, null, null, null, null],
                                    borderColor: 'rgb(34,197,94)',
                                    backgroundColor: 'rgba(34,197,94,0.08)',
                                    borderWidth: 2.5,
                                    pointRadius: 4,
                                    pointBackgroundColor: 'rgb(34,197,94)',
                                    fill: true,
                                    tension: 0.3,
                                    spanGaps: false,
                                },
                                {
                                    label: 'Cartera esperada (USD M)',
                                    data: [1.1, 13.3, 13.3, 13.3, 13.3, null, null, null, null, null, null, null],
                                    borderColor: 'rgb(96,165,250)',
                                    backgroundColor: 'transparent',
                                    borderWidth: 2,
                                    borderDash: [5,4],
                                    pointRadius: 3,
                                    pointBackgroundColor: 'rgb(96,165,250)',
                                    fill: false,
                                    tension: 0.35,
                                },
                                {
                                    label: 'Meta anual (USD M)',
                                    data: [12.4,12.4,12.4,12.4,12.4,12.4,12.4,12.4,12.4,12.4,12.4,12.4],
                                    borderColor: 'rgb(251,191,36)',
                                    backgroundColor: 'transparent',
                                    borderWidth: 1.5,
                                    borderDash: [8,4],
                                    pointRadius: 0,
                                    fill: false,
                                    tension: 0,
                                },
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => ' ' + ctx.dataset.label + ': \$' + (ctx.parsed.y !== null ? ctx.parsed.y.toFixed(1) + 'M' : '—')
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { color: 'rgba(148,163,184,0.15)' },
                                    ticks: { font: { size: 11 }, color: '#94a3b8' }
                                },
                                y: {
                                    grid: { color: 'rgba(148,163,184,0.15)' },
                                    ticks: {
                                        font: { size: 11 }, color: '#94a3b8',
                                        callback: v => '\$' + v + 'M'
                                    },
                                    min: 0,
                                    suggestedMax: 16,
                                }
                            }
                        }
                    });
                 ">
                <canvas></canvas>
            </div>
        </div>

        {{-- Chart B: Avance semanal por proyecto (ocupa 2/5) --}}
        <div class="xl:col-span-2 rounded-lg border border-slate-200 bg-white p-6">
            <div class="mb-4">
                <h3 class="text-base font-medium text-slate-900">Avance semanal por proyecto</h3>
                <p class="mt-0.5 text-sm text-slate-500">% de cierre — semanas Q1–Q2 2026</p>
            </div>
            <div class="relative h-64 w-full"
                 x-data
                 x-init="
                    const weeks = ['S1','S2','S3','S4','S5','S6','S7','S8','S9','S10','S11','S12','S13','S14','S15','S16','S17','S18','S19','S20'];
                    const palette = [
                        { border: 'rgb(239,68,68)',   bg: 'rgba(239,68,68,0.06)'   },
                        { border: 'rgb(59,130,246)',  bg: 'rgba(59,130,246,0.06)'  },
                        { border: 'rgb(16,185,129)',  bg: 'rgba(16,185,129,0.06)'  },
                        { border: 'rgb(245,158,11)',  bg: 'rgba(245,158,11,0.06)'  },
                        { border: 'rgb(139,92,246)',  bg: 'rgba(139,92,246,0.06)'  },
                    ];
                    const proyectos = [
                        { name: 'IGASAMEX – HTS 30x4&quot; Oleofinos CP152/25', data: [55,58,62,66,70,73,76,79,81,83,85,87,88,89,90,91,92,93,94,95] },
                        { name: 'NATURGY – Anillos separadores',              data: [0,0,0,5,10,15,20,25,30,35,38,41,44,47,50,53,55,57,59,61] },
                        { name: 'ICA – HTSF 24x24 Naucalpan',                data: [0,0,0,5,8,12,16,19,21,23,25,26,27,28,29,30,31,32,33,35] },
                        { name: 'ESENTIA – DLSS 36&quot; Villa de Reyes',     data: [0,0,0,0,0,0,5,8,12,15,17,19,21,23,24,25,26,27,28,29] },
                        { name: 'ENGIE – HTP 42x24 VDR San Luis Potosi',     data: [0,0,0,0,0,0,0,5,9,13,16,18,20,22,23,24,25,26,27,28] },
                    ];
                    const ctx = $el.querySelector('canvas').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: weeks,
                            datasets: proyectos.map((p, i) => ({
                                label: p.name,
                                data: p.data,
                                borderColor: palette[i].border,
                                backgroundColor: palette[i].bg,
                                borderWidth: 2,
                                pointRadius: 2,
                                pointHoverRadius: 5,
                                fill: false,
                                tension: 0.3,
                            }))
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 10, boxHeight: 10,
                                        font: { size: 10 }, color: '#64748b',
                                        padding: 8,
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => ' ' + ctx.dataset.label + ': ' + ctx.parsed.y + '%'
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { color: 'rgba(148,163,184,0.15)' },
                                    ticks: { font: { size: 10 }, color: '#94a3b8', maxTicksLimit: 10 }
                                },
                                y: {
                                    grid: { color: 'rgba(148,163,184,0.15)' },
                                    ticks: {
                                        font: { size: 10 }, color: '#94a3b8',
                                        callback: v => v + '%'
                                    },
                                    min: 0, max: 100,
                                    stepSize: 25,
                                }
                            }
                        }
                    });
                 ">
                <canvas></canvas>
            </div>
        </div>
    </div>

    {{-- Section 2: Charts grid --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Chart 2: Pipeline por sublínea --}}
        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <h3 class="text-base font-medium text-slate-900">Pipeline por sublínea</h3>
            <p class="mt-1 text-sm text-slate-500">Distribución por línea de negocio</p>
            <div class="mt-4">
                @php
                    $sublineaColors = ['bg-gpt-500', 'bg-blue-500', 'bg-green-500', 'bg-amber-500', 'bg-slate-400'];
                    $sublineaTextColors = ['text-gpt-700', 'text-blue-700', 'text-green-700', 'text-amber-700', 'text-slate-600'];
                    $totalProyectos = max($porSublinea->sum(), 1);
                @endphp
                @foreach($porSublinea as $nombre => $count)
                    @php $idx = $loop->index % 5; @endphp
                    <div class="flex items-center gap-2 py-1.5">
                        <span class="w-24 text-xs text-slate-500 text-right">{{ $nombre }}</span>
                        <div class="flex-1 h-4 rounded bg-slate-100 relative overflow-hidden">
                            <div class="absolute inset-y-0 left-0 {{ $sublineaColors[$idx] }} rounded" style="width: {{ min(round(($count / $totalProyectos) * 100), 100) }}%"></div>
                        </div>
                        <span class="w-14 text-xs font-medium {{ $sublineaTextColors[$idx] }} text-right">{{ round(($count / $totalProyectos) * 100) }}%</span>
                    </div>
                @endforeach
                @if($porSublinea->isEmpty())
                    <p class="text-sm text-slate-400 text-center py-4">Sin datos de sublínea</p>
                @endif
            </div>
        </div>

        {{-- Chart 3: Pipeline por estado --}}
        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <h3 class="text-base font-medium text-slate-900">Pipeline por estado</h3>
            <p class="mt-1 text-sm text-slate-500">Distribución de oportunidades por estado actual</p>
            <div class="mt-4 space-y-2">
                @php
                    $estadoLabels = [
                        'en_revision' => 'En revisión', 'cotizando' => 'Cotizando', 'cotizado' => 'Cotizado',
                        'presentado' => 'Presentado', 'adjudicado_pendiente' => 'Adj. pend.', 'adjudicado_firmado' => 'Adjudicado',
                        'en_ejecucion' => 'En ejecución', 'en_cierre' => 'En cierre', 'cerrado' => 'Cerrado',
                        'cancelado' => 'Cancelado', 'perdido' => 'Perdido', 'archivado' => 'Archivado',
                    ];
                    $estadoColors = [
                        'en_revision' => 'bg-amber-400', 'cotizando' => 'bg-blue-400', 'cotizado' => 'bg-gpt-400',
                        'presentado' => 'bg-gpt-500', 'adjudicado_pendiente' => 'bg-amber-500', 'adjudicado_firmado' => 'bg-green-500',
                        'en_ejecucion' => 'bg-green-600', 'en_cierre' => 'bg-gpt-500', 'cerrado' => 'bg-slate-400',
                        'cancelado' => 'bg-slate-300', 'perdido' => 'bg-red-400', 'archivado' => 'bg-slate-300',
                    ];
                    $maxEstado = max($porEstado->max() ?? 1, 1);
                @endphp
                @foreach($estadoLabels as $key => $label)
                    @php $count = $porEstado->get($key, 0); @endphp
                    @if($count > 0 || in_array($key, ['en_revision', 'cotizando', 'presentado', 'adjudicado_firmado', 'en_ejecucion']))
                        <div class="flex items-center gap-2">
                            <span class="w-24 text-xs text-slate-500 text-right">{{ $label }}</span>
                            <div class="flex-1 h-3 rounded bg-slate-100 relative overflow-hidden">
                                <div class="absolute inset-y-0 left-0 {{ $estadoColors[$key] ?? 'bg-slate-400' }} rounded" style="width: {{ min(round(($count / $maxEstado) * 100), 100) }}%"></div>
                            </div>
                            <span class="w-8 text-xs font-medium text-slate-700 text-right">{{ $count }}</span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- SECCIÓN: Evolución por probabilidad de adjudicación          --}}
    {{-- ============================================================ --}}
    <div class="mt-8">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-base font-medium text-slate-900">Evolución por probabilidad de adjudicación</h3>
                <p class="mt-0.5 text-sm text-slate-500">Monto bruto (ofertas emitidas) vs. cartera esperada (ponderada = bruto × %) por proyecto y mes</p>
            </div>
            <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500">
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-5 rounded-sm bg-slate-300/60 border border-slate-300"></span>Ofertas emitidas</span>
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-5 rounded-sm bg-blue-400/70"></span>Cartera esperada</span>
                <span class="inline-flex items-center gap-1.5 border-l border-slate-200 pl-4">
                    <svg class="h-3 w-5" viewBox="0 0 20 2"><line x1="0" y1="1" x2="20" y2="1" stroke="#94a3b8" stroke-width="2" stroke-dasharray="4 3"/></svg>
                    Prob. %
                </span>
            </div>
        </div>

        {{-- Combined overview --}}
        <div class="rounded-lg border border-slate-200 bg-white p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-1 mb-4">
                <div>
                    <h4 class="text-sm font-semibold text-slate-800">Vista general — todos los proyectos</h4>
                    <p class="text-xs text-slate-400 mt-0.5">Evolución mensual de la probabilidad de adjudicación · 2026</p>
                </div>
                <p class="text-xs text-slate-400">
                    Líneas de referencia: <span class="font-medium text-slate-500">25% · 50% · 75% ·</span>
                    <span class="font-semibold text-green-600">Concentrada 100%</span>
                </p>
            </div>
            <div class="relative h-64 w-full" x-data x-init="
                const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
                const ctx = $el.querySelector('canvas').getContext('2d');
                const refLines = [
                    { label:'_100', data:Array(12).fill(100), borderColor:'rgba(34,197,94,0.35)',   borderWidth:1.5, borderDash:[6,4], pointRadius:0, fill:false, tension:0 },
                    { label:'_75',  data:Array(12).fill(75),  borderColor:'rgba(148,163,184,0.3)',  borderWidth:1,   borderDash:[4,3], pointRadius:0, fill:false, tension:0 },
                    { label:'_50',  data:Array(12).fill(50),  borderColor:'rgba(148,163,184,0.3)',  borderWidth:1,   borderDash:[4,3], pointRadius:0, fill:false, tension:0 },
                    { label:'_25',  data:Array(12).fill(25),  borderColor:'rgba(148,163,184,0.3)',  borderWidth:1,   borderDash:[4,3], pointRadius:0, fill:false, tension:0 },
                ];
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: meses,
                        datasets: [
                            ...refLines,
                            { label:'IGASAMEX – HTS 30x4&quot; Oleofinos (75%)',
                              data:[75,75,75,75,75,null,null,null,null,null,null,null],
                              borderColor:'rgb(59,130,246)', backgroundColor:'rgba(59,130,246,0.06)',
                              borderWidth:2.5, pointRadius:4, pointBackgroundColor:'rgb(59,130,246)',
                              fill:false, tension:0, stepped:true, spanGaps:false },
                            { label:'MOLPER – DLSS 6&quot; 600# Hidalgo (25%)',
                              data:[10,25,25,25,25,null,null,null,null,null,null,null],
                              borderColor:'rgb(16,185,129)', backgroundColor:'rgba(16,185,129,0.06)',
                              borderWidth:2.5, pointRadius:4, pointBackgroundColor:'rgb(16,185,129)',
                              fill:false, tension:0, stepped:true, spanGaps:false },
                            { label:'SEDENA – Frente 11 Tren Mex-Qro (25%)',
                              data:[null,10,25,25,25,null,null,null,null,null,null,null],
                              borderColor:'rgb(245,158,11)', backgroundColor:'rgba(245,158,11,0.06)',
                              borderWidth:2.5, pointRadius:4, pointBackgroundColor:'rgb(245,158,11)',
                              fill:false, tension:0, stepped:true, spanGaps:false },
                        ]
                    },
                    options: {
                        responsive:true, maintainAspectRatio:false,
                        interaction:{ mode:'index', intersect:false },
                        plugins: {
                            legend:{
                                display:true, position:'bottom',
                                labels:{ filter: i => !i.text.startsWith('_'), boxWidth:12, boxHeight:12, font:{size:11}, color:'#64748b', padding:12 }
                            },
                            tooltip:{
                                filter: i => !i.dataset.label.startsWith('_'),
                                callbacks:{ label: c => ' ' + c.dataset.label + ': ' + c.parsed.y + '%' }
                            }
                        },
                        scales:{
                            x:{ grid:{color:'rgba(148,163,184,0.15)'}, ticks:{font:{size:11},color:'#94a3b8'} },
                            y:{ min:0, max:110,
                                grid:{color:'rgba(148,163,184,0.1)'},
                                ticks:{ font:{size:11}, color:'#94a3b8',
                                        callback: v => v===100 ? 'Conc.' : v===75 ? '75%' : v===50 ? '50%' : v===25 ? '25%' : v===10 ? '10%' : v===0 ? '0' : '' }
                            }
                        }
                    }
                });
            ">
                <canvas></canvas>
            </div>
        </div>

        {{-- Individual project charts --}}
        @php
            $proyectosChart = [
                [
                    'nombre'   => 'SEDENA – Frente 11 Tren Méx-Qro',
                    'sector'   => 'Infraestructura · Tren México-Querétaro',
                    'bruto'    => 36.37,
                    'probs'    => [null, 10, 25, 25, 25, null, null, null, null, null, null, null],
                    'border'   => 'rgb(59,130,246)',
                    'barBg'    => 'rgba(59,130,246,0.65)',
                    'barBdr'   => 'rgb(59,130,246)',
                ],
                [
                    'nombre'   => 'MOLPER – DLSS 6" 600# Hidalgo',
                    'sector'   => 'Gasoductos · Hidalgo',
                    'bruto'    => 2.98,
                    'probs'    => [10, 25, 25, 25, 25, null, null, null, null, null, null, null],
                    'border'   => 'rgb(16,185,129)',
                    'barBg'    => 'rgba(16,185,129,0.65)',
                    'barBdr'   => 'rgb(16,185,129)',
                ],
                [
                    'nombre'   => 'ESENTIA – DLSS 36" Gasoducto Villa de Reyes',
                    'sector'   => 'Gasoductos · Jalisco',
                    'bruto'    => 1.26,
                    'probs'    => [null, 25, 25, 25, 25, null, null, null, null, null, null, null],
                    'border'   => 'rgb(245,158,11)',
                    'barBg'    => 'rgba(245,158,11,0.65)',
                    'barBdr'   => 'rgb(245,158,11)',
                ],
            ];
        @endphp
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            @foreach($proyectosChart as $proy)
                @php
                    $ponderados   = array_map(fn($p) => $p !== null ? round($proy['bruto'] * $p / 100, 2) : null, $proy['probs']);
                    $brutosActivos = array_map(fn($p) => $p !== null ? $proy['bruto'] : null, $proy['probs']);
                    $maxPonderado = max(array_filter($ponderados, fn($v) => $v !== null) ?: [0]);
                    $eficiencia   = $proy['bruto'] > 0 ? round($maxPonderado / $proy['bruto'] * 100, 1) : 0;
                    $efColor      = $eficiencia >= 75 ? 'text-green-600' : ($eficiencia >= 50 ? 'text-amber-600' : 'text-blue-600');
                    $suggestedMax = $proy['bruto'] * 1.25;
                @endphp
                <div class="rounded-lg border border-slate-200 bg-white p-5">
                    <div class="mb-3 border-b border-slate-100 pb-3">
                        <h4 class="text-sm font-semibold text-slate-800 leading-snug">{{ $proy['nombre'] }}</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $proy['sector'] }}</p>
                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs">
                            <span class="text-slate-500">Ofertas emitidas: <strong class="text-slate-700">${{ number_format($proy['bruto'], 1) }}M</strong></span>
                            <span class="text-slate-500">Cartera esperada máx: <strong class="text-slate-700">${{ number_format($maxPonderado, 2) }}M</strong></span>
                            <span class="{{ $efColor }} font-semibold">Eficiencia {{ $eficiencia }}%</span>
                        </div>
                    </div>
                    <div class="relative h-52 w-full" x-data x-init="
                        const ctx = $el.querySelector('canvas').getContext('2d');
                        new Chart(ctx, {
                            data: {
                                labels: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
                                datasets: [
                                    {
                                        type:'bar', label:'Ofertas emitidas',
                                        data: {{ json_encode($brutosActivos) }},
                                        backgroundColor:'rgba(148,163,184,0.18)',
                                        borderColor:'rgba(148,163,184,0.35)',
                                        borderWidth:1, borderRadius:3,
                                        yAxisID:'yMonto', order:3,
                                    },
                                    {
                                        type:'bar', label:'Cartera esperada',
                                        data: {{ json_encode($ponderados) }},
                                        backgroundColor:'{{ $proy['barBg'] }}',
                                        borderColor:'{{ $proy['barBdr'] }}',
                                        borderWidth:1, borderRadius:3,
                                        yAxisID:'yMonto', order:2,
                                    },
                                    {
                                        type:'line', label:'Probabilidad %',
                                        data: {{ json_encode($proy['probs']) }},
                                        borderColor:'{{ $proy['border'] }}',
                                        backgroundColor:'transparent',
                                        borderWidth:2.5,
                                        pointRadius:4, pointBackgroundColor:'{{ $proy['border'] }}',
                                        fill:false, tension:0, stepped:true, spanGaps:false,
                                        yAxisID:'yProb', order:1,
                                    },
                                ]
                            },
                            options: {
                                responsive:true, maintainAspectRatio:false,
                                interaction:{ mode:'index', intersect:false },
                                plugins:{
                                    legend:{
                                        display:true, position:'bottom',
                                        labels:{ boxWidth:10, boxHeight:10, font:{size:10}, color:'#64748b', padding:8 }
                                    },
                                    tooltip:{
                                        callbacks:{
                                            label: i => i.dataset.yAxisID === 'yProb'
                                                ? ' Prob.: ' + i.parsed.y + '%'
                                                : ' ' + i.dataset.label + ': $' + i.parsed.y + 'M'
                                        }
                                    }
                                },
                                scales:{
                                    x:{ grid:{display:false}, ticks:{font:{size:9}, color:'#94a3b8'} },
                                    yMonto:{
                                        type:'linear', position:'left',
                                        min:0, suggestedMax: {{ $suggestedMax }},
                                        grid:{color:'rgba(148,163,184,0.15)'},
                                        ticks:{ font:{size:10}, color:'#94a3b8', callback: v => '$'+v+'M' },
                                    },
                                    yProb:{
                                        type:'linear', position:'right',
                                        min:0, max:110,
                                        grid:{display:false},
                                        ticks:{
                                            font:{size:10}, color:'#94a3b8',
                                            callback: v => v===100?'Conc.':v===75?'75%':v===50?'50%':v===25?'25%':v===10?'10%':v===0?'0':'',
                                        },
                                    },
                                }
                            }
                        });
                    ">
                        <canvas></canvas>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Section 3: Top 5 urgent --}}
    <div class="mt-6 rounded-lg border border-slate-200 bg-white overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
            <h3 class="text-base font-medium text-slate-900">Top 5 — Requieren atención urgente</h3>
            <p class="mt-0.5 text-sm text-slate-500">Oportunidades sin actividad reciente o próximas a vencer</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">CP</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tech Ref</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Cliente</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Días sin act.</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Líder</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Acción sugerida</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($oportunidadesAtencion as $item)
                        @php
                            $lider = $item->lider;
                            $liderInitials = $lider ? collect(explode(' ', $lider->name ?? 'N A'))->map(fn($n) => mb_strtoupper(mb_substr($n, 0, 1)))->take(2)->implode('') : '—';
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors cursor-pointer" wire:navigate href="{{ route('oportunidades.show', $item->id) }}">
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-mono font-medium text-slate-900">{{ $item->cp }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600 max-w-[140px] truncate" title="{{ $item->ref_tecnica ?? '' }}">{{ $item->ref_tecnica ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-900">{{ $item->cliente->razon_social ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <x-badge :status="$item->estado" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $item->dias_sin_actividad > 15 ? 'bg-gpt-red-100 text-gpt-red-800' : ($item->dias_sin_actividad > 7 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $item->dias_sin_actividad }}d
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if($lider)
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gpt-100 text-xs font-semibold text-gpt-700">{{ $liderInitials }}</span>
                                        <span class="text-sm text-slate-700">{{ $lider->name }}</span>
                                    </div>
                                @else
                                    <span class="text-sm text-slate-400">Sin asignar</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="text-sm {{ $item->accion_sugerida === 'Cotizar' ? 'text-gpt-600' : ($item->accion_sugerida === 'Presentar' ? 'text-amber-600' : 'text-slate-600') }}">
                                    {{ $item->accion_sugerida }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center">
                                <p class="text-sm text-slate-500">No hay oportunidades urgentes en este momento.</p>
                                <p class="mt-1 text-xs text-slate-400">Todas las oportunidades tienen seguimiento reciente.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Section 4: 3 Health cards --}}
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('proyectos.asignaciones') }}" class="block rounded-lg border border-slate-200 bg-white p-4 hover:border-gpt-300 hover:shadow-sm transition-all">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium text-slate-700">Carga del equipo</h4>
                <svg class="h-5 w-5 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </div>
            <div class="mt-3 flex items-end justify-between">
                <div>
                    <p class="text-3xl font-semibold {{ $cargaEquipo['avg'] > 6 ? 'text-gpt-red-600' : ($cargaEquipo['avg'] > 4 ? 'text-amber-600' : 'text-green-600') }}">{{ number_format($cargaEquipo['avg'], 1) }}</p>
                    <p class="text-xs text-slate-500">Oport. por líder</p>
                </div>
                <div class="text-right">
                    @if($cargaEquipo['sobrecarga'] > 0)
                        <span class="inline-flex items-center gap-1 rounded-full bg-gpt-red-100 px-2 py-0.5 text-xs font-medium text-gpt-red-700">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01"/></svg>
                            {{ $cargaEquipo['sobrecarga'] }} con sobrecarga
                        </span>
                    @endif
                </div>
            </div>
            <p class="mt-1 text-xs text-slate-400">{{ $cargaEquipo['total'] }} líderes activos</p>
        </a>

        <a href="{{ route('oportunidades.index') }}" class="block rounded-lg border border-slate-200 bg-white p-4 hover:border-gpt-300 hover:shadow-sm transition-all">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium text-slate-700">Dossiers en riesgo</h4>
                <svg class="h-5 w-5 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </div>
            <div class="mt-3">
                <p class="text-3xl font-semibold {{ $dossiersRiesgo > 2 ? 'text-gpt-red-600' : ($dossiersRiesgo > 0 ? 'text-amber-600' : 'text-green-600') }}">{{ $dossiersRiesgo }}</p>
                <p class="text-xs text-slate-500">Con completitud &lt;80%</p>
            </div>
            @if($dossiersRiesgo > 0)
                <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">
                    Requieren revisión
                </span>
            @endif
        </a>

        <a href="{{ route('proyectos.index') }}" class="block rounded-lg border border-slate-200 bg-white p-4 hover:border-gpt-300 hover:shadow-sm transition-all">
            <div class="flex items-center justify-between">
                <h4 class="text-sm font-medium text-slate-700">Post-Mortems pendientes</h4>
                <svg class="h-5 w-5 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
            </div>
            <div class="mt-3">
                <p class="text-3xl font-semibold {{ $postMortems > 0 ? 'text-amber-600' : 'text-green-600' }}">{{ $postMortems }}</p>
                <p class="text-xs text-slate-500">Proyectos cerrados sin análisis</p>
            </div>
            @if($postMortems > 0)
                <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">
                    {{ $postMortems }} pendientes
                </span>
            @endif
        </a>
    </div>

    {{-- Section 5: Cierre Gerencial Snapshot --}}
    <div class="mt-6">
        <a href="{{ route('finanzas.cierres') }}" class="block rounded-lg border border-slate-200 bg-white p-6 hover:border-gpt-300 hover:shadow-sm transition-all">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-medium text-slate-900">Cierre Gerencial</h3>
                    <p class="mt-0.5 text-sm text-slate-500">Snapshot financiero — Ofertas emitidas vs Adjudicado</p>
                </div>
                <svg class="h-5 w-5 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
            </div>
            @php
                $enRevisionMonto = $pipeline->whereNotIn('estado', ['cerrado', 'cancelado', 'perdido', 'archivado'])->sum(fn($p) => $p->cotizaciones->max('precio_venta_final') ?? 0);
                $totalCierre = max($pipelineMonto + $enRevisionMonto, 1);
                $pipePct = $totalCierre > 0 ? ($pipelineMonto / $totalCierre) * 100 : 0;
                $adjPct = $totalCierre > 0 ? ($adjudicadoMonto / $totalCierre) * 100 : 0;
                $revPct = $totalCierre > 0 ? (100 - $pipePct - $adjPct) : 0;
            @endphp
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <p class="text-xs font-medium text-slate-500">Ofertas emitidas</p>
                    <p class="mt-1 text-xl font-semibold text-gpt-600">$ {{ number_format($pipelineMonto, 0, '.', ',') }}</p>
                    <p class="text-[11px] text-slate-400">{{ number_format($pipelineTotal) }} oportunidades</p>
                </div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <p class="text-xs font-medium text-slate-500">Adjudicado</p>
                    <p class="mt-1 text-xl font-semibold text-green-600">$ {{ number_format($adjudicadoMonto, 0, '.', ',') }}</p>
                    <p class="text-[11px] text-slate-400">{{ $adjudicadosCount }} proyectos</p>
                </div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <p class="text-xs font-medium text-slate-500">En revisión/cotización</p>
                    <p class="mt-1 text-xl font-semibold text-blue-600">$ {{ number_format($enRevisionMonto, 0, '.', ',') }}</p>
                    <p class="text-[11px] text-slate-400">{{ number_format($pipePct, 1) }}% del total</p>
                </div>
            </div>
            <div class="mt-4 flex items-end gap-1 h-10">
                <div class="bg-gpt-400 rounded-t flex-1" style="height: {{ 10 + min($pipePct, 90) }}%"></div>
                <div class="bg-green-400 rounded-t flex-1" style="height: {{ 10 + min($adjPct, 90) }}%"></div>
                <div class="bg-blue-400 rounded-t flex-1" style="height: {{ 10 + min(abs($revPct), 90) }}%"></div>
            </div>
            <div class="mt-3 flex items-center justify-between">
                <p class="text-sm text-slate-600">Cartera total</p>
                <p class="text-lg font-semibold text-slate-900">$ {{ number_format($totalCierre, 0, '.', ',') }}</p>
            </div>
        </a>
    </div>
</div>