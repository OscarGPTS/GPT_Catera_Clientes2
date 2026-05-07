<x-layouts.app>
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
            <h2 class="text-2xl font-medium text-slate-900">{{ $saludo }}, {{ $nombre_usuario ?? 'Usuario' }}</h2>
            <p class="mt-1 text-sm text-slate-500">Vista ejecutiva &middot; {{ $quarter_label ?? 'Q1 2026' }} &middot; Datos al {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
        </div>
    </x-slot>

    {{-- Global filters row --}}
    <div class="flex flex-wrap items-center gap-3">
        {{-- Período selector --}}
        <div class="inline-flex rounded-lg border border-slate-200 bg-white p-0.5" role="group" x-data="{ periodo: '{{ request('periodo', 'trimestre') }}' }">
            @foreach(['mes' => 'Mes', 'trimestre' => 'Trimestre', 'anio' => 'Año', 'custom' => 'Custom'] as $key => $label)
                <a href="{{ route('ejecutivo.dashboard', array_merge(request()->except('page'), ['periodo' => $key])) }}"
                   class="rounded-md px-3 py-1.5 text-sm font-medium transition-colors {{ request('periodo', 'trimestre') == $key ? 'bg-gpt-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Cliente multi-select --}}
        <div class="relative" x-data="{ open: false, selected: {{ json_encode(request('clientes', [])) }} }" @click.outside="open = false">
            <button type="button" @click="open = !open"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Cliente
                <span x-show="selected.length > 0" x-text="'(' + selected.length + ')'" class="text-xs text-gpt-600"></span>
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-cloak class="absolute left-0 z-30 mt-1 w-64 rounded-lg border border-slate-200 bg-white shadow-lg">
                <div class="p-2">
                    <input type="text" placeholder="Buscar cliente..." class="w-full rounded-md border border-slate-200 px-3 py-1.5 text-sm focus:border-gpt-600 focus:ring-gpt-600 mb-2">
                </div>
                <div class="max-h-48 overflow-y-auto px-2 pb-2 space-y-0.5">
                    @foreach($clientes ?? [] as $cliente)
                        <label class="flex items-center gap-2 rounded-md px-2 py-1.5 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" :value="'{{ $cliente->id }}'" x-model="selected"
                                   class="h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                            <span class="text-sm text-slate-700">{{ $cliente->nombre }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="border-t border-slate-100 px-2 py-1.5">
                    <button type="button" @click="selected = []; open = false" class="text-xs text-slate-500 hover:text-gpt-600">Limpiar</button>
                </div>
            </div>
        </div>

        {{-- Sublínea multi-select --}}
        <div class="relative" x-data="{ open: false, selected: {{ json_encode(request('sublineas', [])) }} }" @click.outside="open = false">
            <button type="button" @click="open = !open"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                Sublínea
                <span x-show="selected.length > 0" x-text="'(' + selected.length + ')'" class="text-xs text-gpt-600"></span>
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-cloak class="absolute left-0 z-30 mt-1 w-56 rounded-lg border border-slate-200 bg-white shadow-lg">
                <div class="p-2 space-y-0.5">
                    @foreach($sublineas ?? [] as $sub)
                        <label class="flex items-center gap-2 rounded-md px-2 py-1.5 hover:bg-slate-50 cursor-pointer">
                            <input type="checkbox" :value="'{{ $sub->id }}'" x-model="selected"
                                   class="h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                            <span class="text-sm text-slate-700">{{ $sub->nombre }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="border-t border-slate-100 px-2 py-1.5">
                    <button type="button" @click="selected = []; open = false" class="text-xs text-slate-500 hover:text-gpt-600">Limpiar</button>
                </div>
            </div>
        </div>

        {{-- Toggle: Incluir SEDENA --}}
        <label class="inline-flex items-center gap-2 cursor-pointer" x-data="{ incluir: {{ json_encode(request('incluir_sedena', '1') == '1') }} }">
            <input type="checkbox" x-model="incluir"
                   class="sr-only peer">
            <div class="relative h-5 w-9 rounded-full bg-slate-200 peer-checked:bg-gpt-600 transition-colors after:absolute after:start-0.5 after:top-0.5 after:h-4 after:w-4 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-4">
            </div>
            <span class="text-sm text-slate-700 whitespace-nowrap">Incluir SEDENA</span>
        </label>

        {{-- Descargar reporte mensual PDF --}}
        <a href="{{ route('ejecutivo.reporte-mensual', request()->query()) }}"
           class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm hover:bg-slate-50 transition-colors ml-auto">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Descargar reporte mensual PDF
        </a>
    </div>

    {{-- SEDENA Alert --}}
    @php $sedena = $sedena_concentracion ?? 82.7; @endphp
    @if($sedena > 50)
        <div class="mt-4 rounded-lg border border-gpt-red-200 bg-gpt-red-50 p-4">
            <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gpt-red-100">
                    <svg class="h-5 w-5 text-gpt-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gpt-red-800">Concentración SEDENA {{ number_format($sedena, 1) }}% del pipeline — Supera el umbral de riesgo del 50%</p>
                    <p class="mt-1 text-xs text-gpt-red-600">La dependencia excesiva de un solo cliente compromete la estabilidad financiera del portafolio. Acciones recomendadas: diversificar clientes objetivo y priorizar adjudicaciones en sectores no gubernamentales.</p>
                </div>
                <a href="{{ route('proyectos.oportunidades', ['cliente' => 'sedena']) }}" class="flex-shrink-0 rounded-lg border border-gpt-red-200 bg-white px-3 py-1.5 text-xs font-medium text-gpt-red-700 hover:bg-gpt-red-100 transition-colors">
                    Ver detalle
                </a>
            </div>
        </div>
    @else
        <div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-4">
            <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-green-100">
                    <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-green-800">Diversificación saludable</p>
                    <p class="mt-1 text-xs text-green-600">La concentración de clientes se mantiene dentro de los umbrales de riesgo aceptables (&lt;50%).</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Section 1: 4 KPI cards --}}
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $pipeline = $pipeline_activo ?? 54700000;
            $pipelineTrend = $pipeline_trend ?? 12;
            $adjudicado = $adjudicado_ytd ?? 8300000;
            $metaAdjudicacion = $meta_adjudicacion ?? 12400000;
            $metaPct = $metaAdjudicacion > 0 ? round(($adjudicado / $metaAdjudicacion) * 100) : 0;
        @endphp
        <x-stat-card
            title="Pipeline total"
            :value="'$ ' . number_format($pipeline, 2, '.', ',')"
            subtitle="{{ $pipelineTrend >= 0 ? '+' : '' }}{{ $pipelineTrend }}% vs Q4 · {{ number_format($pipeline_count ?? 42) }} oport."
            color="blue"
        />
        <x-stat-card
            title="Adjudicado"
            :value="'$ ' . number_format($adjudicado, 2, '.', ',')"
            subtitle="{{ number_format($adjudicado_count ?? 11) }} proyectos"
            color="green"
        />
        <x-stat-card
            title="Hit rate"
            value="{{ $hit_rate_conteo ?? 22 }}% / {{ $hit_rate_monto ?? 15 }}%"
            subtitle="Conteo / Monto · +3% vs Q4"
            color="amber"
        />
        <x-stat-card
            title="Concentración SEDENA"
            value="{{ number_format($sedena, 1) }}%"
            subtitle="Del pipeline total"
            color="red"
        />
    </div>

    {{-- Adjudicado meta progress --}}
    <div class="mt-4 rounded-lg border border-slate-200 bg-white p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-slate-700">Progreso hacia meta de adjudicación</span>
            <span class="text-sm text-slate-500">{{ number_format($metaPct) }}% — $ {{ number_format($adjudicado, 0, '.', ',') }} de $ {{ number_format($metaAdjudicacion, 0, '.', ',') }}</span>
        </div>
        <div class="h-2.5 w-full rounded-full bg-slate-100">
            <div class="h-2.5 rounded-full bg-green-500 transition-all" style="width: {{ min($metaPct, 100) }}%"></div>
        </div>
    </div>

    {{-- Section 2: 4 Charts grid (2x2) --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Chart 1: Concentración por cliente (bar chart) --}}
        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <h3 class="text-base font-medium text-slate-900">Concentración por cliente</h3>
            <p class="mt-1 text-sm text-slate-500">Distribución del pipeline por cliente principal</p>
            <div class="mt-4">
                <div class="border-2 border-dashed border-slate-200 rounded-lg p-8 text-center text-slate-400">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                    </svg>
                    <p class="mt-2 text-sm font-medium">Concentración por cliente</p>
                    <p class="mt-1 text-xs">Barras horizontales — SEDENA en rojo con línea de referencia 50%</p>
                    <div class="mt-4 space-y-2 max-w-[280px] mx-auto">
                        <div class="flex items-center gap-2">
                            <span class="w-20 text-xs text-slate-500 text-right">SEDENA</span>
                            <div class="flex-1 h-4 rounded bg-gpt-red-200 relative overflow-hidden"><div class="absolute inset-y-0 left-0 bg-gpt-red-500 rounded" style="width: 82.7%"></div></div>
                            <span class="w-12 text-xs font-medium text-gpt-red-600 text-right">82.7%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-20 text-xs text-slate-500 text-right">PEMEX</span>
                            <div class="flex-1 h-4 rounded bg-blue-100 relative overflow-hidden"><div class="absolute inset-y-0 left-0 bg-blue-400 rounded" style="width: 8.5%"></div></div>
                            <span class="w-12 text-xs font-medium text-slate-600 text-right">8.5%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-20 text-xs text-slate-500 text-right">CFE</span>
                            <div class="flex-1 h-4 rounded bg-green-100 relative overflow-hidden"><div class="absolute inset-y-0 left-0 bg-green-400 rounded" style="width: 5.2%"></div></div>
                            <span class="w-12 text-xs font-medium text-slate-600 text-right">5.2%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-20 text-xs text-slate-500 text-right">Otros</span>
                            <div class="flex-1 h-4 rounded bg-slate-100 relative overflow-hidden"><div class="absolute inset-y-0 left-0 bg-slate-300 rounded" style="width: 3.6%"></div></div>
                            <span class="w-12 text-xs font-medium text-slate-600 text-right">3.6%</span>
                        </div>
                    </div>
                    <div class="mt-4 relative max-w-[280px] mx-auto">
                        <div class="absolute left-0 right-0 border-t-2 border-dashed border-gpt-red-300" style="top: 0">
                            <span class="absolute -top-3.5 right-0 text-[10px] text-gpt-red-400">Límite 50%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart 2: Pipeline por sublínea (donut chart) --}}
        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <h3 class="text-base font-medium text-slate-900">Pipeline por sublínea</h3>
            <p class="mt-1 text-sm text-slate-500">Distribución del monto estimado por línea de negocio</p>
            <div class="mt-4">
                <div class="border-2 border-dashed border-slate-200 rounded-lg p-8 text-center text-slate-400">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z"/>
                    </svg>
                    <p class="mt-2 text-sm font-medium">Pipeline por sublínea</p>
                    <p class="mt-1 text-xs">Gráfica de dona — Anillos HTP/LSP/VLV/SOL/SG con leyenda, total al centro</p>
                    <div class="mt-4 flex flex-wrap items-center justify-center gap-3">
                        <span class="inline-flex items-center gap-1 text-xs"><span class="h-2.5 w-2.5 rounded-full bg-gpt-600"></span> HTP 38%</span>
                        <span class="inline-flex items-center gap-1 text-xs"><span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span> LSP 24%</span>
                        <span class="inline-flex items-center gap-1 text-xs"><span class="h-2.5 w-2.5 rounded-full bg-green-500"></span> VLV 18%</span>
                        <span class="inline-flex items-center gap-1 text-xs"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> SOL 13%</span>
                        <span class="inline-flex items-center gap-1 text-xs"><span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span> SG 7%</span>
                    </div>
                    <p class="mt-2 text-2xl font-semibold text-slate-700">$54.7M</p>
                    <p class="text-xs text-slate-400">Total pipeline</p>
                </div>
            </div>
        </div>

        {{-- Chart 3: Adjudicaciones mensuales (line chart) --}}
        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <h3 class="text-base font-medium text-slate-900">Adjudicaciones mensuales</h3>
            <p class="mt-1 text-sm text-slate-500">Monto adjudicado mensualmente — últimos 5 meses</p>
            <div class="mt-4">
                <div class="border-2 border-dashed border-slate-200 rounded-lg p-8 text-center text-slate-400">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>
                    </svg>
                    <p class="mt-2 text-sm font-medium">Adjudicaciones mensuales</p>
                    <p class="mt-1 text-xs">Gráfica de línea — 5 puntos de datos (ene - may 2026)</p>
                    <div class="mt-4 flex items-end justify-center gap-3 h-16">
                        <div class="flex flex-col items-center">
                            <div class="w-10 rounded-t bg-green-400" style="height: 28px"></div>
                            <span class="mt-1 text-[10px]">Ene</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="w-10 rounded-t bg-green-400" style="height: 20px"></div>
                            <span class="mt-1 text-[10px]">Feb</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="w-10 rounded-t bg-green-400" style="height: 56px"></div>
                            <span class="mt-1 text-[10px]">Mar</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="w-10 rounded-t bg-green-400" style="height: 40px"></div>
                            <span class="mt-1 text-[10px]">Abr</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <div class="w-10 rounded-t bg-green-400" style="height: 48px"></div>
                            <span class="mt-1 text-[10px]">May</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chart 4: Hit rate trimestral (line chart, 2 series) --}}
        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <h3 class="text-base font-medium text-slate-900">Hit rate trimestral</h3>
            <p class="mt-1 text-sm text-slate-500">Evolución comparativa conteo vs monto — últimos 4 trimestres</p>
            <div class="mt-4">
                <div class="border-2 border-dashed border-slate-200 rounded-lg p-8 text-center text-slate-400">
                    <svg class="mx-auto h-10 w-10 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941"/>
                    </svg>
                    <p class="mt-2 text-sm font-medium">Hit rate trimestral</p>
                    <p class="mt-1 text-xs">Gráfica de línea — 2 series (conteo azul, monto naranja) · 4 trimestres</p>
                    <div class="mt-4 flex items-center justify-center gap-6">
                        <span class="inline-flex items-center gap-1 text-xs"><span class="h-2.5 w-2.5 rounded-full bg-blue-500"></span> Conteo</span>
                        <span class="inline-flex items-center gap-1 text-xs"><span class="h-2.5 w-2.5 rounded-full bg-gpt-600"></span> Monto</span>
                    </div>
                    <div class="mt-3 grid grid-cols-4 gap-2 text-center">
                        @foreach(['Q2 2025', 'Q3 2025', 'Q4 2025', 'Q1 2026'] as $qtr)
                            <div>
                                <p class="text-[10px] text-slate-400">{{ $qtr }}</p>
                                <p class="text-xs font-medium text-blue-500">19%</p>
                                <p class="text-xs font-medium text-gpt-600">12%</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 3: Top 5 urgent --}}
    <div class="mt-6 rounded-lg border border-slate-200 bg-white overflow-hidden">
        <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
            <h3 class="text-base font-medium text-slate-900">Top 5 — Requieren atención urgente</h3>
            <p class="mt-0.5 text-sm text-slate-500">Oportunidades ordenadas por puntuación de urgencia — sin actividad reciente o próximas a vencer</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">CP</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tech Ref</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Cliente</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Monto</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Probabilidad</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Días sin act.</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Líder</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Acción sugerida</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($urgentes ?? [] as $item)
                        @php
                            $liderInitials = collect(explode(' ', $item->lider->name ?? 'N A'))->map(fn($n) => mb_strtoupper(mb_substr($n, 0, 1)))->take(2)->implode('');
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors cursor-pointer" @click="window.location='{{ route('proyectos.show', $item) }}'">
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-mono font-medium text-slate-900">{{ $item->cp }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600 max-w-[140px] truncate" title="{{ $item->ref_tecnica ?? '' }}">{{ $item->ref_tecnica ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-900">{{ $item->cliente->nombre ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-slate-900">$ {{ number_format($item->monto_estimado ?? $item->monto ?? 0, 0, '.', ',') }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                @php $prob = $item->probabilidad ?? 0; @endphp
                                <div class="inline-flex items-center gap-2">
                                    <div class="h-1.5 w-12 rounded-full bg-slate-100">
                                        <div class="h-1.5 rounded-full {{ $prob >= 70 ? 'bg-green-500' : ($prob >= 40 ? 'bg-amber-400' : 'bg-gpt-red-400') }}" style="width: {{ $prob }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-slate-700">{{ $prob }}%</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ ($item->dias_sin_actividad ?? 0) > 15 ? 'bg-gpt-red-100 text-gpt-red-800' : (($item->dias_sin_actividad ?? 0) > 7 ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600') }}">
                                    {{ $item->dias_sin_actividad ?? 0 }}d
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-gpt-100 text-xs font-semibold text-gpt-700">{{ $liderInitials }}</span>
                                    <span class="text-sm text-slate-700">{{ $item->lider->name ?? '—' }}</span>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="text-sm {{ ($item->accion_sugerida ?? '') === 'Cotizar' ? 'text-gpt-600' : (($item->accion_sugerida ?? '') === 'Presentar' ? 'text-amber-600' : 'text-slate-600') }}">
                                    {{ $item->accion_sugerida ?? 'Dar seguimiento' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-10 text-center">
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
        @php
            $cargaEquipo = $carga_equipo ?? ['avg' => 4.9, 'sobrecarga' => 1, 'total' => 8];
            $dossiersRiesgo = $dossiers_riesgo ?? 2;
            $postMortems = $post_mortems_pendientes ?? 3;
        @endphp
        <a href="{{ route('equipo.carga') }}" class="block rounded-lg border border-slate-200 bg-white p-4 hover:border-gpt-300 hover:shadow-sm transition-all">
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

        <a href="{{ route('dossiers.riesgo') }}" class="block rounded-lg border border-slate-200 bg-white p-4 hover:border-gpt-300 hover:shadow-sm transition-all">
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

        <a href="{{ route('postmortems.pendientes') }}" class="block rounded-lg border border-slate-200 bg-white p-4 hover:border-gpt-300 hover:shadow-sm transition-all">
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
        <a href="{{ url('/finanzas/cierres') }}" class="block rounded-lg border border-slate-200 bg-white p-6 hover:border-gpt-300 hover:shadow-sm transition-all">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-medium text-slate-900">Cierre Gerencial</h3>
                    <p class="mt-0.5 text-sm text-slate-500">Snapshot financiero acumulado — SAT + Devengado + Pipeline</p>
                </div>
                <svg class="h-5 w-5 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
            </div>
            @php
                $sat = $cierre_sat ?? 12500000;
                $devengado = $cierre_devengado ?? 22300000;
                $pipelineCierre = $cierre_pipeline ?? 54700000;
                $totalCierre = $sat + $devengado + $pipelineCierre;
                $satPct = $totalCierre > 0 ? ($sat / $totalCierre) * 100 : 0;
                $devPct = $totalCierre > 0 ? ($devengado / $totalCierre) * 100 : 0;
                $pipePct = $totalCierre > 0 ? ($pipelineCierre / $totalCierre) * 100 : 0;
            @endphp
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <p class="text-xs font-medium text-slate-500">SAT</p>
                    <p class="mt-1 text-xl font-semibold text-blue-600">$ {{ number_format($sat, 0, '.', ',') }}</p>
                    <p class="text-[11px] text-slate-400">{{ number_format($satPct, 1) }}% del total</p>
                </div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <p class="text-xs font-medium text-slate-500">Devengado</p>
                    <p class="mt-1 text-xl font-semibold text-indigo-600">$ {{ number_format($devengado, 0, '.', ',') }}</p>
                    <p class="text-[11px] text-slate-400">{{ number_format($devPct, 1) }}% del total</p>
                </div>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-3">
                    <p class="text-xs font-medium text-slate-500">Pipeline</p>
                    <p class="mt-1 text-xl font-semibold text-gpt-600">$ {{ number_format($pipelineCierre, 0, '.', ',') }}</p>
                    <p class="text-[11px] text-slate-400">{{ number_format($pipePct, 1) }}% del total</p>
                </div>
            </div>
            {{-- Waterfall mini-bars --}}
            <div class="mt-4 flex items-end gap-1 h-10">
                <div class="bg-blue-400 rounded-t flex-1" style="height: {{ 10 + ($satPct * 0.7) }}%"></div>
                <div class="bg-indigo-400 rounded-t flex-1" style="height: {{ 10 + ($devPct * 0.7) }}%"></div>
                <div class="bg-gpt-400 rounded-t flex-1" style="height: {{ 10 + ($pipePct * 0.7) }}%"></div>
            </div>
            <div class="mt-3 flex items-center justify-between">
                <p class="text-sm text-slate-600">Total cartera + pipeline</p>
                <p class="text-lg font-semibold text-slate-900">$ {{ number_format($totalCierre, 0, '.', ',') }}</p>
            </div>
        </a>
    </div>
</x-layouts.app>
