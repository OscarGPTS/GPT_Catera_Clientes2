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
                <h3 class="text-base font-medium text-slate-900">Status Ofertas 2026 — Seguimiento de adjudicación</h3>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                <span class="inline-flex items-center gap-1.5">
                    <span class="inline-block h-3 w-5 rounded-sm bg-emerald-500/70"></span> (100%)
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="inline-block h-3 w-5 rounded-sm bg-cyan-500/70"></span> (75%)
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="inline-block h-3 w-5 rounded-sm bg-blue-500/70"></span> (50%)
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="inline-block h-3 w-5 rounded-sm bg-amber-500/70"></span> (25%)
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="inline-block h-3 w-5 rounded-sm bg-red-400/70"></span>Cancelada / Cerrada (0%)
                </span>
            </div>
        </div>

        {{-- Main Multi Axis Line Chart --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-4">
                <div>
                    <h4 class="text-sm font-semibold text-slate-800">Adjudicación por oferta · Nov 2025 – May 2026</h4>
                </div>
                <div class="text-xs text-slate-400 shrink-0">
                    Total ofertas: <strong class="text-slate-600">$54.73M USD</strong>
                </div>
            </div>
            <div class="relative h-[520px] w-full" x-data x-init="
                const d = window._statusOfertas;
                const ctx = $el.querySelector('canvas').getContext('2d');
                const refLines = [
                    { label: '_ref100', data: Array(7).fill(100), borderColor: 'rgba(34,197,94,0.25)', borderWidth: 1.5, borderDash: [6,4], pointRadius: 0, fill: false, tension: 0, yAxisID: 'yProb', order: 99 },
                    { label: '_ref75',  data: Array(7).fill(75),  borderColor: 'rgba(6,182,212,0.2)',   borderWidth: 1,   borderDash: [4,3], pointRadius: 0, fill: false, tension: 0, yAxisID: 'yProb', order: 99 },
                    { label: '_ref50',  data: Array(7).fill(50),  borderColor: 'rgba(59,130,246,0.2)',  borderWidth: 1,   borderDash: [4,3], pointRadius: 0, fill: false, tension: 0, yAxisID: 'yProb', order: 99 },
                    { label: '_ref25',  data: Array(7).fill(25),  borderColor: 'rgba(245,158,11,0.2)', borderWidth: 1,   borderDash: [4,3], pointRadius: 0, fill: false, tension: 0, yAxisID: 'yProb', order: 99 },
                    { label: '_ref0',   data: Array(7).fill(0),   borderColor: 'rgba(239,68,68,0.15)',  borderWidth: 1,   borderDash: [4,3], pointRadius: 0, fill: false, tension: 0, yAxisID: 'yProb', order: 99 },
                ];
                const projectLines = d.proyectos.map((p, i) => ({
                    label: p.nombre + ' (' + p.cp + ')',
                    data: p.probs,
                    borderColor: p.borderColor,
                    backgroundColor: p.bgColor,
                    borderWidth: 1.8,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointStyle: 'circle',
                    pointBackgroundColor: p.borderColor,
                    fill: false,
                    tension: 0.2,
                    spanGaps: false,
                    yAxisID: 'yProb',
                    order: 1,
                    _monto: p.monto,
                }));
                const datasets = [
                    ...refLines,
                    ...projectLines,
                    {
                        type: 'bar',
                        label: 'Cartera ponderada (USD M)',
                        data: d.ponderadoByMonth,
                        backgroundColor: 'rgba(99,102,241,0.18)',
                        borderColor: 'rgba(99,102,241,0.5)',
                        borderWidth: 1,
                        borderRadius: 3,
                        yAxisID: 'yMonto',
                        order: 50,
                    },
                    {
                        type: 'bar',
                        label: 'Ofertas activas total (USD M)',
                        data: d.montoByMonth,
                        backgroundColor: 'rgba(148,163,184,0.12)',
                        borderColor: 'rgba(148,163,184,0.35)',
                        borderWidth: 1,
                        borderRadius: 3,
                        yAxisID: 'yMonto',
                        order: 51,
                    },
                ];
                new Chart(ctx, {
                    type: 'line',
                    data: { labels: d.months, datasets },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    filter: item => !item.text.startsWith('_ref'),
                                    boxWidth: 12, boxHeight: 8,
                                    font: { size: 10 }, color: '#64748b',
                                    padding: 6,
                                    usePointStyle: true,
                                    pointStyle: 'line',
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(c) {
                                        if (c.dataset.label.startsWith('_ref')) return null;
                                        if (c.dataset.yAxisID === 'yMonto') {
                                            return ' ' + c.dataset.label + ': $' + (c.parsed.y !== null ? c.parsed.y.toFixed(2) + 'M' : '—');
                                        }
                                        var monto = c.dataset._monto ? ' · $' + (c.parsed.y !== null ? (c.dataset._monto * c.parsed.y / 100).toFixed(3) : '0') + 'M pond.' : '';
                                        return ' ' + c.dataset.label + ': ' + (c.parsed.y !== null ? c.parsed.y + '%' : '—') + monto;
                                    }
                                }
                            },
                        },
                        scales: {
                            x: {
                                grid: { color: 'rgba(148,163,184,0.12)' },
                                ticks: { font: { size: 11 }, color: '#94a3b8' },
                            },
                            yProb: {
                                type: 'linear',
                                position: 'left',
                                min: 0,
                                max: 110,
                                grid: { color: 'rgba(148,163,184,0.1)' },
                                ticks: {
                                    font: { size: 11 }, color: '#64748b',
                                    stepSize: 25,
                                    callback: function(v) {
                                        if (v === 100) return '100%';
                                        if (v === 75)  return '75%';
                                        if (v === 50)  return '50%';
                                        if (v === 25)  return '25%';
                                        if (v === 0)   return '0%';
                                        return '';
                                    }
                                },
                                title: {
                                    display: true,
                                    text: '% Adjudicación',
                                    font: { size: 12, weight: '500' },
                                    color: '#64748b',
                                },
                            },
                            yMonto: {
                                type: 'linear',
                                position: 'right',
                                min: 0,
                                grid: { display: false },
                                ticks: {
                                    font: { size: 11 }, color: '#94a3b8',
                                    callback: function(v) { return '$' + v + 'M'; },
                                },
                                title: {
                                    display: true,
                                    text: 'USD Millones',
                                    font: { size: 12, weight: '500' },
                                    color: '#94a3b8',
                                },
                            },
                        },
                    },
                });
            ">
                <canvas></canvas>
            </div>
        </div>

        {{-- Data table: Status Ofertas 2026 --}}
        <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-semibold text-slate-800">Detalle de ofertas — Status Ofertas 2026</h4>
                    <p class="text-xs text-slate-400 mt-0.5">CP · Cliente · Monto USD · % adjudicación actual por mes · 0% = Cancelada/Cerrada</p>
                </div>
                <div class="text-xs text-slate-400">
                    {{ count($statusOfertasData['proyectos']) }} ofertas · $54.73M total
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
                                $probColor = $currentProb >= 75 ? 'text-green-600 bg-green-50' : ($currentProb >= 50 ? 'text-blue-600 bg-blue-50' : ($currentProb >= 25 ? 'text-amber-600 bg-amber-50' : 'text-red-600 bg-red-50'));
                                $probLabel  = $currentProb >= 100 ? 'Contratada' : ($currentProb >= 75 ? 'Casi Probable' : ($currentProb >= 50 ? 'Probable' : ($currentProb >= 25 ? 'Posible' : 'Cancelada')));
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
                                        $cellColor = $prob === null ? 'bg-transparent text-slate-300' : ($prob >= 100 ? 'bg-green-100 text-green-800 font-semibold' : ($prob >= 75 ? 'bg-teal-50 text-teal-700 font-medium' : ($prob >= 50 ? 'bg-blue-50 text-blue-700 font-medium' : ($prob >= 25 ? 'bg-amber-50 text-amber-700' : ($prob === 0 ? 'bg-red-50 text-red-700 font-medium' : 'bg-transparent text-slate-300')))));
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
    {{-- ============================================================ --}}
    {{-- SECCIÓN: Evolución de Cartera x Ejecutar 2026              --}}
    {{-- ============================================================ --}}
    <div class="mt-8">
        @php
            $kpis = $carteraKpis;
        @endphp

        {{-- KPI cards --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <div class="rounded-lg border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Ofertas emitidas</p>
                <p class="mt-1 text-2xl font-semibold text-slate-900">${{ number_format($kpis['bruto'], 2, '.', ',') }}M</p>
                <p class="mt-0.5 text-xs text-slate-400">Cartera total bruta USD</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Cartera esperada</p>
                <p class="mt-1 text-2xl font-semibold text-indigo-600">${{ number_format($kpis['esperado'], 2, '.', ',') }}M</p>
                <p class="mt-0.5 text-xs text-slate-400">Monto ponderado × probabilidad</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total ofertas</p>
                <p class="mt-1 text-2xl font-semibold text-slate-900">{{ $kpis['count'] }}</p>
                <p class="mt-0.5 text-xs text-slate-400">Ofertas activas en pipeline</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Eficiencia ponderada</p>
                <p class="mt-1 text-2xl font-semibold {{ $kpis['eficiencia'] >= 50 ? 'text-green-600' : 'text-amber-600' }}">{{ $kpis['eficiencia'] }}%</p>
                <p class="mt-0.5 text-xs text-slate-400">Esperada / Emitida × 100</p>
            </div>
        </div>

        {{-- Chart: cumulative by probability threshold --}}
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2 mb-4">
                <div>
                    <h3 class="text-base font-medium text-slate-900">Evolución de cartera por ejecutar 2026</h3>
                    <p class="mt-0.5 text-sm text-slate-500">Cartera acumulada por umbral de probabilidad · Monto bruto vs. cartera esperada</p>
                </div>
                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 shrink-0">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="inline-block h-3 w-5 rounded-sm bg-indigo-500"></span>Ofertas emitidas
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <span class="inline-block h-3 w-8 border-t-2 border-dashed border-amber-500"></span>Cartera esperada
                    </span>
                </div>
            </div>
            <div class="relative h-80 w-full" x-data x-init="
                const k = window._statusOfertas.carteraKpis;
                const d = window._statusOfertas.carteraData;
                const ctx = $el.querySelector('canvas').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: d.labels,
                        datasets: [
                            {
                                label: 'Ofertas emitidas (USD M)',
                                data: d.bruto,
                                borderColor: 'rgb(99,102,241)',
                                backgroundColor: 'rgba(99,102,241,0.08)',
                                borderWidth: 2.5,
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                pointStyle: 'circle',
                                pointBackgroundColor: 'rgb(99,102,241)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                fill: true,
                                tension: 0.3,
                                yAxisID: 'y',
                            },
                            {
                                label: 'Cartera esperada (USD M)',
                                data: d.esperado,
                                borderColor: 'rgb(245,158,11)',
                                backgroundColor: 'transparent',
                                borderWidth: 2.5,
                                borderDash: [8, 4],
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                pointStyle: 'circle',
                                pointBackgroundColor: 'rgb(245,158,11)',
                                pointBorderColor: '#fff',
                                pointBorderWidth: 2,
                                fill: false,
                                tension: 0.3,
                                yAxisID: 'y',
                            },
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
                                    font: { size: 11 }, color: '#64748b', padding: 16,
                                    usePointStyle: true, pointStyle: 'circle',
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(c) {
                                        var val = c.parsed.y !== null ? c.parsed.y.toFixed(2) : '—';
                                        var prefix = c.dataset.label.includes('esperada') ? ' Esperada' : ' Emitidas';
                                        return prefix + ': $' + val + 'M';
                                    },
                                    afterBody: function(items) {
                                        if (!items.length) return '';
                                        var idx = items[0].dataIndex;
                                        var cnt = d.counts[idx];
                                        return 'Ofertas en umbral: ' + cnt;
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

            {{-- Detail table by threshold --}}
            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold text-slate-600">Umbral</th>
                            <th class="px-4 py-2 text-right font-semibold text-slate-600">Ofertas emitidas</th>
                            <th class="px-4 py-2 text-right font-semibold text-slate-600">Cartera esperada</th>
                            <th class="px-4 py-2 text-right font-semibold text-slate-600">N° ofertas</th>
                            <th class="px-4 py-2 text-right font-semibold text-slate-600">Eficiencia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($carteraData['labels'] as $idx => $label)
                            @php
                                $bruto = $carteraData['bruto'][$idx];
                                $esperado = $carteraData['esperado'][$idx];
                                $count = $carteraData['counts'][$idx];
                                $eff = $bruto > 0 ? round($esperado / $bruto * 100, 1) : 0;
                                $effColor = $eff >= 50 ? 'text-green-600' : ($eff >= 25 ? 'text-amber-600' : 'text-red-600');
                                $barW = $kpis['bruto'] > 0 ? round($bruto / $kpis['bruto'] * 100) : 0;
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-2 font-medium text-slate-800">{{ $label }}</td>
                                <td class="px-4 py-2 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <div class="w-24 h-2 rounded-full bg-slate-100 overflow-hidden">
                                            <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $barW }}%"></div>
                                        </div>
                                        <span class="font-mono text-slate-700">${{ number_format($bruto, 2) }}M</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-right font-mono text-amber-600">${{ number_format($esperado, 2) }}M</td>
                                <td class="px-4 py-2 text-right text-slate-700">{{ $count }}</td>
                                <td class="px-4 py-2 text-right font-semibold {{ $effColor }}">{{ $eff }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
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
                <h3 class="text-base font-medium text-slate-900">Evolución de cartera x ejecutar 2026</h3>
                <p class="mt-0.5 text-sm text-slate-500">Monto bruto por mes y banda de probabilidad · Nov 2025 – May 2026</p>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-5 rounded-sm bg-emerald-500"></span>100% Contratada</span>
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-5 rounded-sm bg-cyan-500"></span>75% Casi Probable</span>
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-5 rounded-sm bg-blue-500"></span>50% Probable</span>
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-5 rounded-sm bg-amber-500"></span>25% Posible</span>
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-5 rounded-sm bg-red-400"></span>0% Cancelada</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
            {{-- Chart (3/5) --}}
            <div class="xl:col-span-3 rounded-xl border border-slate-200 bg-white shadow-sm p-6">
                <div class="relative h-96 w-full" x-data x-init="
                    const evo = window._statusOfertas.carteraEvolucion;
                    const ctx = $el.querySelector('canvas').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: evo.months,
                            datasets: [
                                { label: '100% Contratada', data: evo.series.p100, backgroundColor: 'rgba(16,185,129,0.8)', stack: 's', borderRadius: 2 },
                                { label: '75% Casi Probable', data: evo.series.p75, backgroundColor: 'rgba(6,182,212,0.8)', stack: 's', borderRadius: 2 },
                                { label: '50% Probable', data: evo.series.p50, backgroundColor: 'rgba(59,130,246,0.8)', stack: 's', borderRadius: 2 },
                                { label: '25% Posible', data: evo.series.p25, backgroundColor: 'rgba(245,158,11,0.8)', stack: 's', borderRadius: 2 },
                                { label: '0% Cancelada', data: evo.series.p0, backgroundColor: 'rgba(248,113,113,0.7)', stack: 's', borderRadius: 2 },
                                {
                                    type: 'line',
                                    label: 'Cartera esperada (ponderada)',
                                    data: evo.ponderado,
                                    borderColor: 'rgb(245,158,11)',
                                    backgroundColor: 'transparent',
                                    borderWidth: 2.5,
                                    borderDash: [8, 4],
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    pointStyle: 'circle',
                                    pointBackgroundColor: 'rgb(245,158,11)',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    fill: false,
                                    tension: 0.3,
                                    yAxisID: 'y',
                                    order: 0,
                                },
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
                                        filter: function(item) { return !item.text.includes('(ponderada)'); },
                                        boxWidth: 12, boxHeight: 8,
                                        font: { size: 10 }, color: '#64748b', padding: 10,
                                        usePointStyle: true,
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
                                    stacked: true,
                                    grid: { color: 'rgba(148,163,184,0.12)' },
                                    ticks: { font: { size: 12, weight: '500' }, color: '#374151' },
                                },
                                y: {
                                    stacked: false,
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
                <h4 class="text-sm font-semibold text-slate-800 mb-1">Por nivel de probabilidad (Ponderación)</h4>
                <p class="text-xs text-slate-400 mb-4">Distribución del valor bruto y cartera ponderada por banda</p>

                <div class="space-y-3 flex-1">
                    @foreach($levelResumen as $lr)
                        @php
                            $bandColors = [
                                'p100' => ['bg' => 'bg-emerald-500', 'text' => 'text-emerald-700', 'bar' => 'bg-emerald-400'],
                                'p75'  => ['bg' => 'bg-cyan-500',    'text' => 'text-cyan-700',    'bar' => 'bg-cyan-400'],
                                'p50'  => ['bg' => 'bg-blue-500',    'text' => 'text-blue-700',    'bar' => 'bg-blue-400'],
                                'p25'  => ['bg' => 'bg-amber-500',   'text' => 'text-amber-700',   'bar' => 'bg-amber-400'],
                                'p0'   => ['bg' => 'bg-red-400',     'text' => 'text-red-700',     'bar' => 'bg-red-300'],
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
                                    <span class="text-xs {{ $c['text'] }} font-medium">${{ number_format($lr['bruto'], 1) }}M</span>
                                </div>
                                <div class="mt-1 h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-1.5 rounded-full {{ $c['bar'] }}" style="width: {{ $barW }}%"></div>
                                </div>
                                <div class="flex items-baseline justify-between gap-2 mt-0.5">
                                    <span class="text-[10px] text-slate-400">{{ $lr['count'] }} oferta{{ $lr['count'] !== 1 ? 's' : '' }} · Ponderado: ${{ number_format($lr['pond'], 1) }}M</span>
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
                        La mayor concentración de valor bruto está en el 50%
                        @foreach($levelResumen as $lr)
                            @if($lr['key'] === 'p50' && $lr['bruto'] > 0)
                                <strong>(${{ number_format($lr['bruto'], 1) }}M)</strong>
                            @endif
                        @endforeach
                        pero la cartera ponderada baja significativamente.
                        @foreach($levelResumen as $lr)
                            @if($lr['key'] === 'p25' && $lr['count'] > 0)
                                Las ofertas al {{ $lr['label'] }} son las más numerosas (<strong>{{ $lr['count'] }}</strong> en total).
                            @endif
                        @endforeach
                        La eficiencia ponderada total es del <strong>{{ $carteraKpis['eficiencia'] }}%</strong>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- SECCIÓN: Distribución por Responsable                       --}}
    {{-- ============================================================ --}}
    <div class="mt-8">
        @php
            $respData = $byResponsable;
            $respTotalBruto = array_sum($respData['bruto']);
            $respTotalPond = array_sum($respData['pond']);
        @endphp

        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-base font-medium text-slate-900">Distribución de cartera por responsable (USD)</h3>
                <p class="mt-0.5 text-sm text-slate-500">Valor bruto vs. cartera ponderada por ejecutivo · Status Ofertas 2026</p>
            </div>
            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-5 rounded-sm bg-indigo-500"></span>Valor bruto</span>
                <span class="inline-flex items-center gap-1.5"><span class="inline-block h-3 w-8 border-t-2 border-dashed border-amber-500"></span>Cartera ponderada</span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
            {{-- Chart (3/5) --}}
            <div class="xl:col-span-3 rounded-xl border border-slate-200 bg-white shadow-sm p-6">
                <div class="relative h-96 w-full" x-data x-init="
                    const resp = window._statusOfertas.byResponsable;
                    const ctx = $el.querySelector('canvas').getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: resp.labels,
                            datasets: [
                                {
                                    label: 'Valor bruto (USD M)',
                                    data: resp.bruto,
                                    backgroundColor: 'rgba(99,102,241,0.7)',
                                    borderColor: 'rgb(99,102,241)',
                                    borderWidth: 1,
                                    borderRadius: 4,
                                    yAxisID: 'y',
                                    order: 2,
                                },
                                {
                                    type: 'line',
                                    label: 'Cartera ponderada (USD M)',
                                    data: resp.pond,
                                    borderColor: 'rgb(245,158,11)',
                                    backgroundColor: 'transparent',
                                    borderWidth: 2.5,
                                    borderDash: [8, 4],
                                    pointRadius: 5,
                                    pointHoverRadius: 7,
                                    pointStyle: 'circle',
                                    pointBackgroundColor: 'rgb(245,158,11)',
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    fill: false,
                                    tension: 0.2,
                                    yAxisID: 'y',
                                    order: 1,
                                },
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
                                        font: { size: 11 }, color: '#64748b', padding: 16,
                                        usePointStyle: true,
                                    },
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(c) {
                                            var prefix = c.dataset.label.includes('ponderada') ? ' Ponderado' : ' Bruto';
                                            return prefix + ': $' + (c.parsed.y !== null ? c.parsed.y.toFixed(2) : '—') + 'M';
                                        },
                                        afterBody: function(items) {
                                            if (!items.length) return '';
                                            var idx = items[0].dataIndex;
                                            return 'Ofertas: ' + resp.counts[idx];
                                        }
                                    }
                                },
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    ticks: { font: { size: 12, weight: '600' }, color: '#374151' },
                                },
                                y: {
                                    grid: { color: 'rgba(148,163,184,0.12)' },
                                    ticks: {
                                        font: { size: 11 }, color: '#94a3b8',
                                        stepSize: 5,
                                        callback: function(v) { return '$' + v + 'M'; },
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
                <h4 class="text-sm font-semibold text-slate-800 mb-1">Por responsable (Ponderación)</h4>
                <p class="text-xs text-slate-400 mb-4">Distribución de valor bruto y cartera ponderada por responsable</p>

                <div class="space-y-3 flex-1">
                    @foreach($respData['labels'] as $idx => $label)
                        @php
                            $b = $respData['bruto'][$idx];
                            $p = $respData['pond'][$idx];
                            $c = $respData['counts'][$idx];
                            $eff = $b > 0 ? round($p / $b * 100, 1) : 0;
                            $barW = $respTotalBruto > 0 ? round($b / $respTotalBruto * 100) : 0;
                            $colors = [
                                ['dot' => 'bg-indigo-500', 'bar' => 'bg-indigo-400', 'eff' => $eff >= 50 ? 'text-green-600' : 'text-amber-600'],
                            ];
                            $effColor = $eff >= 50 ? 'text-green-600' : 'text-amber-600';
                        @endphp
                        <div class="flex items-start gap-2">
                            <span class="mt-1.5 inline-block h-2.5 w-2.5 rounded-full bg-indigo-500 shrink-0"></span>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-baseline justify-between gap-2">
                                    <span class="text-xs font-semibold text-slate-800">{{ $label }}</span>
                                    <span class="text-xs text-indigo-700 font-medium">${{ number_format($b, 2) }}M</span>
                                </div>
                                <div class="mt-1 h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                                    <div class="h-1.5 rounded-full bg-indigo-400" style="width: {{ $barW }}%"></div>
                                </div>
                                <div class="flex items-baseline justify-between gap-2 mt-0.5">
                                    <span class="text-[10px] text-slate-400">{{ $c }} oferta{{ $c !== 1 ? 's' : '' }} · Pond: ${{ number_format($p, 2) }}M</span>
                                    <span class="text-[10px] {{ $effColor }} font-semibold">Ef. {{ $eff }}%</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 pt-4 border-t border-slate-200">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg bg-slate-50 p-3 text-center">
                            <p class="text-[10px] font-medium text-slate-500 uppercase">Bruto total</p>
                            <p class="mt-0.5 text-lg font-bold text-slate-900">${{ number_format($respTotalBruto, 1) }}M</p>
                        </div>
                        <div class="rounded-lg bg-amber-50 p-3 text-center">
                            <p class="text-[10px] font-medium text-amber-600 uppercase">Esperado</p>
                            <p class="mt-0.5 text-lg font-bold text-amber-700">${{ number_format($respTotalPond, 1) }}M</p>
                        </div>
                    </div>
                    <div class="mt-2 text-center">
                        <p class="text-xs text-slate-500">Eficiencia ponderada total: <strong class="{{ $carteraKpis['eficiencia'] >= 50 ? 'text-green-600' : 'text-amber-600' }}">{{ $carteraKpis['eficiencia'] }}%</strong></p>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100">
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        @foreach($respData['labels'] as $idx => $label)
                            @if($respData['bruto'][$idx] > 0 && $respData['bruto'][$idx] / $respTotalBruto > 0.1)
                                <strong>{{ $label }}</strong> concentra el {{ round($respData['bruto'][$idx] / $respTotalBruto * 100) }}% del valor bruto (${{ number_format($respData['bruto'][$idx], 1) }}M)
                                @if($respData['counts'][$idx] >= 2)
                                    , gestionando {{ $respData['counts'][$idx] }} ofertas
                                @endif.
                            @endif
                        @endforeach
                        SEDENA distorsiona la escala: sin ella, la cartera del resto suma apenas ${{ number_format($respTotalBruto - $respData['bruto'][array_search('Aquiles G', $respData['labels'])], 1) }}M brutos.
                        La eficiencia ponderada total es del <strong>{{ $carteraKpis['eficiencia'] }}%</strong> — del total bruto de ${{ number_format($respTotalBruto, 1) }}M, la expectativa estadística de adjudicación es ${{ number_format($respTotalPond, 1) }}M.
                    </p>
                </div>
            </div>
        </div>
    </div>


</div>