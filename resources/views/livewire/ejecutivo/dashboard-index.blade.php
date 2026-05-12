<script>window._dashData = @json($chartData); window._statusOfertas = @json($statusOfertasData);</script>
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

    {{-- Global filters row (hidden — no dynamic filtering for Status Ofertas) --}}

    {{-- ============================================================ --}}
    {{-- SECCIÓN: Status Ofertas 2026 — Multi Axis Line Chart        --}}
    {{-- ============================================================ --}}
    <div class="mt-6">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-base font-medium text-slate-900">Ofertas {{ \Carbon\Carbon::now()->year }}</h3>
            </div>
            {{--
            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                <span class="inline-flex items-center gap-1.5">
                    <span class="inline-block h-3 w-5 rounded-sm bg-emerald-500/70"></span> Contratada (100%)
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="inline-block h-3 w-5 rounded-sm bg-cyan-500/70"></span> Probable (75%)
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="inline-block h-3 w-5 rounded-sm bg-amber-500/70"></span> Posible (25%)
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="inline-block h-3 w-5 rounded-sm bg-red-400/70"></span> Remoto (10%)
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="inline-block h-3 w-5 rounded-sm bg-slate-400/70"></span> Perdida (0%)
                </span>
            </div> --}}
        </div>


        {{-- Data table: Status Ofertas 2026 --}}
        <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-semibold text-slate-800">Detalle de ofertas — Status Ofertas 2026</h4>
                   
                </div>
                <div class="text-xs text-slate-400">
                    {{ count($statusOfertasData['proyectos']) }} ofertas · ${{ number_format(array_sum(array_column($statusOfertasData['proyectos'], 'monto')), 2) }}M total
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-2 py-2 text-left font-semibold text-slate-600 sticky left-0 bg-slate-50 z-10 min-w-[180px]">Proyecto (CP)</th>
                            <th class="px-2 py-2 text-right font-semibold text-slate-600">Monto USD</th>
                            @foreach($statusOfertasData['months'] as $m)
                                <th class="px-1.5 py-2 text-center font-semibold text-slate-600 whitespace-nowrap">{{ $m }}</th>
                            @endforeach
                            <th class="px-2 py-2 text-center font-semibold text-slate-600">Actual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($statusOfertasData['proyectos'] as $p)
                            @php
                                $currentProb = 0;
                                for ($i = 6; $i >= 0; $i--) {
                                    if ($p['probs'][$i] !== null) { $currentProb = $p['probs'][$i]; break; }
                                }
                                $probColor = $currentProb >= 75 ? 'text-green-600 bg-green-50' : ($currentProb >= 25 ? 'text-amber-600 bg-amber-50' : ($currentProb >= 10 ? 'text-red-600 bg-red-50' : 'text-slate-500 bg-slate-50'));
                                $probLabel  = $currentProb >= 100 ? 'Contratada' : ($currentProb >= 75 ? 'Probable' : ($currentProb >= 25 ? 'Posible' : ($currentProb >= 10 ? 'Remoto' : 'Perdida')));
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-2 py-1.5 font-medium text-slate-800 sticky left-0 bg-white z-10 whitespace-nowrap" style="border-right: 1px solid #e2e8f0;">
                                    <span class="inline-block w-2.5 h-2.5 rounded-full mr-1.5" style="background-color: {{ $p['borderColor'] }}"></span>
                                    {{ $p['nombre'] }}
                                    <span class="text-slate-400 ml-1">{{ $p['cp'] }}</span>
                                </td>
                                <td class="px-2 py-1.5 text-right font-mono text-slate-700">${{ number_format($p['monto'] * 1000000, 0, '.', ',') }}</td>
                                @foreach($p['probs'] as $prob)
                                    @php
                                        $cellColor = $prob === null ? 'bg-transparent text-slate-300' : ($prob >= 100 ? 'bg-green-100 text-green-800 font-semibold' : ($prob >= 75 ? 'bg-teal-50 text-teal-700 font-medium' : ($prob >= 25 ? 'bg-amber-50 text-amber-700' : ($prob >= 10 ? 'bg-red-50 text-red-700' : ($prob === 0 ? 'bg-slate-50 text-slate-500 font-medium' : 'bg-transparent text-slate-300')))));
                                    @endphp
                                    <td class="px-1.5 py-1.5 text-center {{ $cellColor }}">{{ $prob !== null ? $prob . '%' : '—' }}</td>
                                @endforeach
                                <td class="px-2 py-1.5 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $probColor }}">
                                        {{ $currentProb }}% · {{ $probLabel }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                        <tr class="font-semibold text-slate-700">
                            <td class="px-2 py-2 sticky left-0 bg-slate-50 z-10" style="border-right: 1px solid #e2e8f0;">Total cartera activa</td>
                            <td class="px-2 py-2 text-right">${{ number_format(array_sum(array_column($statusOfertasData['proyectos'], 'monto')) * 1000000, 0, '.', ',') }}</td>
                            @foreach($statusOfertasData['ponderadoByMonth'] as $idx => $pond)
                                @php $totalAct = $statusOfertasData['montoByMonth'][$idx]; @endphp
                                <td class="px-1.5 py-2 text-center text-slate-500">
                                    <span class="block text-indigo-600 font-medium">${{ number_format($pond, 2) }}M</span>
                                    <span class="block text-[10px] text-slate-400">de ${{ number_format($totalAct, 1) }}M</span>
                                </td>
                            @endforeach
                            <td class="px-2 py-2 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold text-indigo-700 bg-indigo-50">
                                    Car. Ponderada
                                </span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>


    {{-- ============================================================ --}}
    {{-- SECCIÓN: Evolución Cartera x Ejecutar – Mes × Banda     --}}
    {{-- ============================================================ --}}
    <div class="mt-8">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-base font-medium text-slate-900">Evolución de la cartera esperada</h3>
            </div>
            {{-- <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-5 rounded-sm bg-emerald-500"></span>100% Contratada</span>
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-5 rounded-sm bg-cyan-500"></span>75% Probable</span>
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-5 rounded-sm bg-amber-500"></span>25% Posible</span>
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-5 rounded-sm bg-red-400"></span>10% Remoto</span>
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-5 rounded-sm bg-slate-400"></span>0% Perdida</span>
            </div> --}}
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
            {{-- Chart (3/5) --}}
            <div class="xl:col-span-3 rounded-xl border border-slate-200 bg-white shadow-sm p-6">
                <div class="relative h-96 w-full" x-data x-init="
                    const evo = window._statusOfertas.carteraEvolucion;
                    const ctx = $el.querySelector('canvas').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: evo.months,
                            datasets: [
                                { label: '100% Contratada', data: evo.series.p100, borderColor: 'rgb(16,185,129)', backgroundColor: 'rgba(16,185,129,0.08)', borderWidth: 2.5, pointRadius: 4, pointHoverRadius: 6, pointStyle: 'circle', pointBackgroundColor: 'rgb(16,185,129)', pointBorderColor: '#fff', pointBorderWidth: 2, fill: true, tension: 0.4 },
                                { label: '75% Probable', data: evo.series.p75, borderColor: 'rgb(6,182,212)', backgroundColor: 'rgba(6,182,212,0.08)', borderWidth: 2.5, pointRadius: 4, pointHoverRadius: 6, pointStyle: 'circle', pointBackgroundColor: 'rgb(6,182,212)', pointBorderColor: '#fff', pointBorderWidth: 2, fill: true, tension: 0.4 },
                                { label: '25% Posible', data: evo.series.p25, borderColor: 'rgb(245,158,11)', backgroundColor: 'rgba(245,158,11,0.08)', borderWidth: 2.5, pointRadius: 4, pointHoverRadius: 6, pointStyle: 'circle', pointBackgroundColor: 'rgb(245,158,11)', pointBorderColor: '#fff', pointBorderWidth: 2, fill: true, tension: 0.4 },
                                { label: '10% Remoto', data: evo.series.p10, borderColor: 'rgb(248,113,113)', backgroundColor: 'rgba(248,113,113,0.08)', borderWidth: 2.5, pointRadius: 4, pointHoverRadius: 6, pointStyle: 'circle', pointBackgroundColor: 'rgb(248,113,113)', pointBorderColor: '#fff', pointBorderWidth: 2, fill: true, tension: 0.4 },
                                { label: '0% Perdida', data: evo.series.p0, borderColor: 'rgb(148,163,184)', backgroundColor: 'rgba(148,163,184,0.08)', borderWidth: 2.5, pointRadius: 4, pointHoverRadius: 6, pointStyle: 'circle', pointBackgroundColor: 'rgb(148,163,184)', pointBorderColor: '#fff', pointBorderWidth: 2, fill: true, tension: 0.4 },
                                { label: 'Cartera esperada (ponderada)', data: evo.ponderado, borderColor: 'rgb(139,92,246)', backgroundColor: 'rgba(139,92,246,0.06)', borderWidth: 3, pointRadius: 5, pointHoverRadius: 7, pointStyle: 'circle', pointBackgroundColor: 'rgb(139,92,246)', pointBorderColor: '#fff', pointBorderWidth: 2, fill: true, tension: 0.4, order: 0 },
                            ]
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
                                        boxWidth: 12, boxHeight: 8,
                                        font: { size: 10 }, color: '#64748b', padding: 10,
                                        usePointStyle: true, pointStyle: 'circle',
                                    },
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(c) {
                                            if (c.dataset.label.includes('ponderada')) return ' Esperada: $' + (c.parsed.y !== null ? c.parsed.y.toFixed(2) : '—') + 'M';
                                            return ' ' + c.dataset.label + ': $' + (c.parsed.y !== null ? c.parsed.y.toFixed(2) : '—') + 'M';
                                        }
                                    }
                                },
                            },
                            scales: {
                                x: {
                                    grid: { color: 'rgba(148,163,184,0.12)' },
                                    ticks: { font: { size: 12, weight: '500' }, color: '#374151' },
                                },
                                y: {
                                    grid: { color: 'rgba(148,163,184,0.12)' },
                                    ticks: {
                                        font: { size: 11 }, color: '#94a3b8',
                                        stepSize: 5,
                                        callback: function(v) { return '$' + v + 'M'; }
                                    },
                                    title: {
                                        display: true,
                                        text: 'Millones USD',
                                        font: { size: 12, weight: '500' },
                                        color: '#64748b',
                                    },
                                    min: 0,
                                },
                            },
                        },
                    });
                ">
                    <canvas></canvas>
                </div>
            </div>

            {{-- Summary card (2/5) --}}
            <div class="xl:col-span-2 rounded-xl border border-slate-200 bg-white shadow-sm p-6 flex flex-col">
                <h4 class="text-sm font-semibold text-slate-800 mb-1">Por nivel de probabilidad </h4>
                <p class="text-xs text-slate-400 mb-4">Distribución de la cartera esperada</p>

                <div class="space-y-3 flex-1">
                    @foreach($levelResumen as $lr)
                        @php
                            $bandColors = [
                                'p100' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-700', 'bar' => 'bg-emerald-400'],
                                'p75'  => ['bg' => 'bg-cyan-500',    'text' => 'text-cyan-700',    'bar' => 'bg-cyan-400'],
                                'p25'  => ['bg' => 'bg-amber-500',   'text' => 'text-amber-700',   'bar' => 'bg-amber-400'],
                                'p10'  => ['bg' => 'bg-red-400',     'text' => 'text-red-700',     'bar' => 'bg-red-300'],
                                'p0'   => ['bg' => 'bg-slate-400',   'text' => 'text-slate-700',   'bar' => 'bg-slate-300'],
                            ];
                            $c = $bandColors[$lr['key']] ?? ['bg' => 'bg-gray-400', 'text' => 'text-gray-700', 'bar' => 'bg-gray-300'];
                            $maxBruto = $carteraKpis['bruto'] > 0 ? $carteraKpis['bruto'] : 1;
                            $barW = $lr['bruto'] > 0 ? round($lr['bruto'] / $maxBruto * 100) : 0;
                        @endphp
                        <div class="flex items-start gap-2">
                            <span class="mt-1 inline-block h-3 w-3 rounded-sm shrink-0 {{ $c['bg'] }}"></span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-baseline justify-between gap-2">
                                    <span class="text-xs font-semibold text-slate-800">{{ $lr['label'] }}</span>
                                    <span class="text-xs {{ $c['text'] }} font-medium">$ {{ number_format($lr['pond'], 1) }} M</span>
                                </div>
                                <div class="mt-1 h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $c['bar'] }}" style="width: {{ $barW }}%"></div>
                                </div>
                                <div class="flex items-baseline justify-between gap-2 mt-0.5">
                                    <span class="text-[10px] text-slate-400">{{ $lr['count'] }} oferta{{ $lr['count'] !== 1 ? 's' : '' }} · Bruto: ${{ number_format($lr['bruto'], 1) }}M</span>
                                    <span class="text-[10px] {{ $lr['eficiencia'] >= 50 ? 'text-green-600' : 'text-slate-500' }}">Ef. {{ $lr['eficiencia'] }}%</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-4 border-t border-slate-200">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-slate-50 p-3 text-center">
                            <p class="text-[10px] font-medium text-slate-500 uppercase">Bruto total</p>
                            <p class="mt-0.5 text-lg font-bold text-slate-900">${{ number_format($carteraKpis['bruto'], 1) }}M</p>
                        </div>
                        <div class="rounded-lg bg-indigo-50 p-3 text-center">
                            <p class="text-[10px] font-medium text-indigo-600 uppercase">Esperado</p>
                            <p class="mt-0.5 text-lg font-bold text-indigo-700">${{ number_format($carteraKpis['esperado'], 1) }}M</p>
                        </div>
                    </div>
                    <div class="mt-2 text-center">
                        <p class="text-xs text-slate-500">Eficiencia ponderada total: <strong class="{{ $carteraKpis['eficiencia'] >= 50 ? 'text-green-600' : 'text-amber-600' }}">{{ $carteraKpis['eficiencia'] }}%</strong></p>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100">
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        @foreach($levelResumen as $lr)
                            @if($lr['key'] === 'p75' && $lr['bruto'] > 0)
                                La mayor concentración de valor bruto está al <strong>{{ $lr['label'] }}</strong> (${{ number_format($lr['bruto'], 1) }}M).
                            @endif
                        @endforeach
                        @foreach($levelResumen as $lr)
                            @if($lr['key'] === 'p25' && $lr['count'] > 0)
                                Las ofertas al <strong>{{ $lr['label'] }}</strong> son las más numerosas (<strong>{{ $lr['count'] }}</strong> en total).
                            @endif
                        @endforeach
                        La eficiencia ponderada total es del <strong>{{ $carteraKpis['eficiencia'] }}%</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>