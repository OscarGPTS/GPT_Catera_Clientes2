<script>window._dashData = @json($chartData); window._statusOfertas = @json($statusOfertasData); window._mesesSinContratacion = @json($mesesSinContratacion);</script>
<script>
function initProjChart(idx) {
    const p = window._statusOfertas.proyectos[idx];
    const months = window._statusOfertas.months;
    const canvas = document.getElementById('chart-proj-' + idx);
    if (!canvas) return;
    const pondData = p.probs.map(v => v !== null ? Math.round(p.monto * (v / 100) * 100) / 100 : null);
    // p.bgColor is already a valid rgba(..., 0.10) string from the server
    const fillColor = p.bgColor || p.borderColor.replace('rgb(', 'rgba(').replace(')', ',0.12)');
    new Chart(canvas.getContext('2d'), {
        type: 'line',
        data: {
            labels: months,
            datasets: [
                {
                    label: 'Probabilidad %',
                    data: p.probs,
                    borderColor: p.borderColor,
                    backgroundColor: fillColor,
                    borderWidth: 2.5,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: p.borderColor,
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'yProb',
                    spanGaps: false
                },
                {
                    label: 'Pond. $M',
                    data: pondData,
                    borderColor: 'rgb(139,92,246)',
                    backgroundColor: 'rgba(139,92,246,0.07)',
                    borderWidth: 2,
                    borderDash: [5, 3],
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(139,92,246)',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointStyle: 'rectRounded',
                    fill: false,
                    tension: 0.4,
                    yAxisID: 'yMonto',
                    spanGaps: false
                }
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
                        font: { size: 10, family: 'Inter, sans-serif' },
                        boxWidth: 12, boxHeight: 8,
                        color: '#64748b',
                        padding: 10,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(15,23,42,0.85)',
                    titleColor: '#e2e8f0',
                    bodyColor: '#cbd5e1',
                    borderColor: 'rgba(148,163,184,0.2)',
                    borderWidth: 1,
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: c => c.dataset.yAxisID === 'yProb'
                            ? '  Prob: ' + (c.parsed.y !== null ? c.parsed.y + '%' : '\u2014')
                            : '  Pond: $' + (c.parsed.y !== null ? c.parsed.y.toFixed(2) : '\u2014') + 'M'
                    }
                }
            },
            scales: {
                x: {
                    border: { display: false },
                    grid: { color: 'rgba(148,163,184,0.10)', drawTicks: false },
                    ticks: { font: { size: 10, family: 'Inter, sans-serif' }, color: '#94a3b8', padding: 6 }
                },
                yProb: {
                    type: 'linear',
                    position: 'left',
                    min: 0,
                    max: 100,
                    border: { display: false },
                    grid: { color: 'rgba(148,163,184,0.10)', drawTicks: false },
                    ticks: { font: { size: 10 }, color: '#64748b', stepSize: 25, padding: 6, callback: v => v + '%' },
                    title: { display: true, text: 'Prob %', font: { size: 9 }, color: '#94a3b8' }
                },
                yMonto: {
                    type: 'linear',
                    position: 'right',
                    border: { display: false },
                    grid: { display: false },
                    ticks: { font: { size: 10 }, color: '#8b5cf6', padding: 6, callback: v => '$' + v + 'M' },
                    title: { display: true, text: 'Pond $M', font: { size: 9 }, color: '#8b5cf6' }
                }
            }
        }
    });
}
</script>
<div>
      
    <div>
        <h2 class="text-2xl font-medium text-slate-900">Hola, {{ $nombre_usuario }}</h2>
        <p class="mt-1 text-sm text-slate-500">Vista ejecutiva </p>
    </div>

    <div class="mt-2">
        
        {{-- Data table: Status Ofertas 2026 --}}
        <div class="mt-6 rounded-xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-semibold text-slate-800">Detalle de Ofertas {{ \Carbon\Carbon::now()->year }}</h4>
                   
                </div>
                <div class="text-xs text-slate-400">
                    {{ count($statusOfertasData['proyectos']) }} ofertas · ${{ number_format(array_sum(array_column($statusOfertasData['proyectos'], 'monto')), 2) }}M total
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-2 py-2 text-left font-semibold text-slate-600 sticky left-0 bg-slate-50 z-10 min-w-[220px]">Proyecto (CP)</th>
                            <th class="px-2 py-2 text-right font-semibold text-slate-600 whitespace-nowrap">Monto USD</th>
                            <th class="px-2 py-2 text-center font-semibold text-slate-600">Actual</th>
                            <th class="px-2 py-2 w-8"></th>
                        </tr>
                    </thead>
                        @foreach($statusOfertasData['proyectos'] as $pIdx => $p)
                            @php
                                $currentProb = 0;
                                for ($i = count($p['probs']) - 1; $i >= 0; $i--) {
                                    if (($p['probs'][$i] ?? null) !== null) { $currentProb = $p['probs'][$i]; break; }
                                }
                                $probColor = $currentProb >= 75 ? 'text-green-600 bg-green-50' : ($currentProb >= 25 ? 'text-amber-600 bg-amber-50' : ($currentProb >= 10 ? 'text-red-600 bg-red-50' : 'text-slate-500 bg-slate-50'));
                                $probLabel  = $currentProb >= 100 ? 'Contratada' : ($currentProb >= 75 ? 'Probable' : ($currentProb >= 25 ? 'Posible' : ($currentProb >= 10 ? 'Remoto' : 'Perdida')));
                            @endphp
                            {{-- One <tbody> per project = shared Alpine scope for both rows --}}
                            <tbody x-data="{ open: false, chartInited: false }"
                                x-effect="if (open && !chartInited) { chartInited = true; $nextTick(() => initProjChart({{ $pIdx }})) }"
                                class="border-b border-slate-100">
                                {{-- Main row --}}
                                <tr class="cursor-pointer hover:bg-slate-50 transition-colors"
                                    @click="open = !open">
                                    <td class="px-2 py-2 font-medium text-slate-800 sticky left-0 bg-white z-10 whitespace-nowrap" style="border-right: 1px solid #e2e8f0;">
                                        <span class="inline-block w-2.5 h-2.5 rounded-full mr-1.5 flex-shrink-0 align-middle" style="background-color: {{ $p['borderColor'] }}"></span>
                                        {{ $p['nombre'] }}
                                        <span class="text-slate-400 ml-1">{{ $p['cp'] }}</span>
                                    </td>
                                    <td class="px-2 py-2 text-right font-mono text-slate-700 whitespace-nowrap">${{ number_format($p['monto'] * 1000000, 0, '.', ',') }}</td>
                                    <td class="px-2 py-2 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $probColor }}">
                                            {{ $currentProb }}% · {{ $probLabel }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-2 text-center text-slate-400 w-8">
                                        <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                    </td>
                                </tr>
                                {{-- Expanded detail row --}}
                                <tr x-show="open" x-cloak class="bg-slate-50/60">
                                    <td colspan="4" class="px-4 pb-4 pt-3">
                                        <div class="grid grid-cols-10 gap-3 items-start">

                                            {{-- Months table: 70% --}}
                                            <div class="col-span-10">
                                                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Detalle por mes</p>
                                                <div class="rounded-lg border border-slate-200 bg-white shadow-inner overflow-x-auto">
                                                    <table class="text-xs w-full">
                                                        <thead>
                                                            <tr class="border-b border-slate-100">
                                                                @foreach($statusOfertasData['months'] as $m)
                                                                    <th class="px-2 py-1.5 text-center font-semibold text-slate-500 whitespace-nowrap bg-slate-50">{{ $m }}</th>
                                                                @endforeach
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                @foreach($p['probs'] as $prob)
                                                                    @php
                                                                        $cellColor = $prob === null ? 'bg-transparent text-slate-300' : ($prob >= 100 ? 'bg-green-100 text-green-800 font-semibold' : ($prob >= 75 ? 'bg-teal-50 text-teal-700 font-medium' : ($prob >= 25 ? 'bg-amber-50 text-amber-700' : ($prob >= 10 ? 'bg-red-50 text-red-700' : ($prob === 0 ? 'bg-slate-50 text-slate-500 font-medium' : 'bg-transparent text-slate-300')))));
                                                                    @endphp
                                                                    <td class="px-2 py-1.5 text-center {{ $cellColor }} whitespace-nowrap">{{ $prob !== null ? $prob . '%' : '—' }}</td>
                                                                @endforeach
                                                            </tr>
                                                            <tr class="border-t border-slate-100">
                                                                @foreach($statusOfertasData['months'] as $mIdx => $mLabel)
                                                                    @php
                                                                        $projProb = $p['probs'][$mIdx] ?? null;
                                                                        $projPond = $projProb !== null ? round($p['monto'] * ($projProb / 100), 2) : null;
                                                                    @endphp
                                                                    <td class="px-2 py-1 text-center text-indigo-600 font-medium whitespace-nowrap text-[10px]">
                                                                        {{ $projPond !== null ? '$' . number_format($projPond, 2) . 'M' : '—' }}
                                                                    </td>
                                                                @endforeach
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            {{-- Chart: 30% --}}
                                            {{--<div class="col-span-3">
                                                <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 mb-1.5">Evolución probabilidad &amp; ponderado</p>
                                                <div class="rounded-lg border border-slate-200 bg-white shadow-inner p-3" style="height:176px;">
                                                    <canvas id="chart-proj-{{ $pIdx }}"></canvas>
                                                </div>
                                            </div>--}}

                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        @endforeach
                    <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                        <tr class="font-semibold text-slate-700">
                            <td class="px-2 py-2 sticky left-0 bg-slate-50 z-10" style="border-right: 1px solid #e2e8f0;">Total cartera activa</td>
                            <td class="px-2 py-2 text-right font-mono">${{ number_format(array_sum(array_column($statusOfertasData['proyectos'], 'monto')) * 1000000, 0, '.', ',') }}</td>
                            <td class="px-2 py-2 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold text-indigo-700 bg-indigo-50">
                                    Car. Ponderada
                                </span>
                            </td>
                            <td></td>
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

    {{-- ============================================================ --}}
    {{-- SECCIÓN: Meses de trabajo sin contratación (Backlog Runway)   --}}
    {{-- ============================================================ --}}
    @php
        $sem = $mesesSinContratacion['semaforo'];
        $semColors = [
            'emerald' => ['bg' => 'bg-emerald-50',  'text' => 'text-emerald-700', 'ring' => 'ring-emerald-200', 'dot' => 'bg-emerald-500',  'line' => 'rgb(16,185,129)',  'fill' => 'rgba(16,185,129,0.12)'],
            'cyan'    => ['bg' => 'bg-cyan-50',     'text' => 'text-cyan-700',    'ring' => 'ring-cyan-200',    'dot' => 'bg-cyan-500',     'line' => 'rgb(6,182,212)',   'fill' => 'rgba(6,182,212,0.12)'],
            'amber'   => ['bg' => 'bg-amber-50',    'text' => 'text-amber-700',   'ring' => 'ring-amber-200',   'dot' => 'bg-amber-500',    'line' => 'rgb(245,158,11)',  'fill' => 'rgba(245,158,11,0.12)'],
            'red'     => ['bg' => 'bg-red-50',      'text' => 'text-red-700',     'ring' => 'ring-red-200',     'dot' => 'bg-red-500',      'line' => 'rgb(239,68,68)',   'fill' => 'rgba(239,68,68,0.12)'],
            'slate'   => ['bg' => 'bg-slate-50',    'text' => 'text-slate-600',   'ring' => 'ring-slate-200',   'dot' => 'bg-slate-400',    'line' => 'rgb(148,163,184)', 'fill' => 'rgba(148,163,184,0.12)'],
        ];
        $sc = $semColors[$sem['color']] ?? $semColors['slate'];
    @endphp

    <div class="mt-8">
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h3 class="text-base font-medium text-slate-900">Meses de trabajo sin contratación</h3>
                <p class="mt-0.5 text-xs text-slate-500">
                    Cuánto trabajo queda por delante si no se firma ningún contrato nuevo
                    <span class="ml-1 text-slate-400">(Backlog contratado ÷ Producción mensual promedio)</span>
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 self-start sm:self-auto rounded-full {{ $sc['bg'] }} {{ $sc['text'] }} px-3 py-1 text-xs font-semibold ring-1 {{ $sc['ring'] }}">
                <span class="inline-block h-2 w-2 rounded-full {{ $sc['dot'] }}"></span>
                {{ $sem['label'] }}
            </span>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-5">
            {{-- KPI tile (2/5) --}}
            <div class="xl:col-span-2 rounded-xl border border-slate-200 bg-white shadow-sm p-6 flex flex-col">
                <p class="text-[10px] font-semibold uppercase tracking-[0.15em] text-slate-400">Runway de cartera</p>

                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-6xl font-light tracking-tight {{ $sc['text'] }}">
                        {{ number_format($mesesSinContratacion['meses'], 1) }}
                    </span>
                    <span class="text-lg text-slate-500 font-medium">meses</span>
                </div>

                <p class="mt-2 text-xs text-slate-500 leading-relaxed">{{ $sem['desc'] }}</p>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-lg bg-slate-50 p-3">
                        <p class="text-[10px] font-medium uppercase text-slate-500">Backlog contratado</p>
                        <p class="mt-1 text-lg font-semibold text-slate-900">${{ number_format($mesesSinContratacion['backlog_m'], 2) }}M</p>
                        <p class="text-[10px] text-slate-400">{{ $mesesSinContratacion['count_contratados'] }} contratos vigentes</p>
                    </div>
                    <div class="rounded-lg bg-indigo-50 p-3">
                        <p class="text-[10px] font-medium uppercase text-indigo-600">Producción / mes</p>
                        <p class="mt-1 text-lg font-semibold text-indigo-700">${{ number_format($mesesSinContratacion['produccion_mensual_m'], 2) }}M</p>
                        <p class="text-[10px] text-indigo-500/80">
                            @if($mesesSinContratacion['tiene_plazo_real'])
                                Plazo promedio: {{ $mesesSinContratacion['plazo_promedio'] }} m
                            @else
                                Plazo estimado: {{ $mesesSinContratacion['plazo_promedio'] }} m (default)
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-t border-slate-100">
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Calculado como
                        <span class="font-mono text-slate-600">Cartera contratada ÷ Producción mensual</span>.
                        La producción mensual se estima distribuyendo el monto de cada contrato sobre su plazo (o sobre {{ $mesesSinContratacion['plazo_promedio'] }} meses promedio cuando no hay plazo capturado).
                    </p>
                </div>
            </div>

            {{-- Burn-down chart (3/5) --}}
            <div class="xl:col-span-3 rounded-xl border border-slate-200 bg-white shadow-sm p-6">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h4 class="text-sm font-semibold text-slate-800">Consumo del backlog</h4>
                        <p class="text-[11px] text-slate-500">Proyección del backlog restante asumiendo que no entran contratos nuevos.</p>
                    </div>
                </div>

                @if($mesesSinContratacion['count_contratados'] === 0)
                    <div class="flex flex-col items-center justify-center h-72 text-center">
                        <svg class="h-10 w-10 text-slate-200 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                        </svg>
                        <p class="text-sm text-slate-500">Aún no hay contratos vigentes registrados.</p>
                        <p class="text-[11px] text-slate-400">Marca oportunidades como adjudicadas (pond. 100%) para alimentar el indicador.</p>
                    </div>
                @else
                    <div class="relative h-72 w-full" x-data x-init="
                        const m = window._mesesSinContratacion;
                        const ctx = $el.querySelector('canvas').getContext('2d');
                        const lineColor = @js($sc['line']);
                        const fillColor = @js($sc['fill']);
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: m.burn_labels,
                                datasets: [{
                                    label: 'Backlog restante',
                                    data: m.burn_serie,
                                    borderColor: lineColor,
                                    backgroundColor: fillColor,
                                    borderWidth: 2.5,
                                    pointRadius: 4,
                                    pointHoverRadius: 6,
                                    pointBackgroundColor: lineColor,
                                    pointBorderColor: '#fff',
                                    pointBorderWidth: 2,
                                    fill: true,
                                    tension: 0.35,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: false },
                                    tooltip: {
                                        callbacks: {
                                            label: c => '  Backlog: $' + c.parsed.y.toFixed(3) + 'M',
                                        }
                                    },
                                    annotation: false,
                                },
                                scales: {
                                    x: {
                                        grid: { color: 'rgba(148,163,184,0.10)' },
                                        ticks: { font: { size: 10 }, color: '#64748b' },
                                    },
                                    y: {
                                        grid: { color: 'rgba(148,163,184,0.10)' },
                                        ticks: { font: { size: 10 }, color: '#94a3b8', callback: v => '$' + v + 'M' },
                                        title: { display: true, text: 'Millones USD', font: { size: 11 }, color: '#64748b' },
                                        min: 0,
                                    },
                                },
                            },
                        });
                    ">
                        <canvas></canvas>
                    </div>

                    {{-- Per-project breakdown --}}
                    <div class="mt-5 pt-4 border-t border-slate-100">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 mb-2">Contratos que sostienen el runway</p>
                        <div class="space-y-2">
                            @php $maxM = collect($mesesSinContratacion['proyectos'])->max('mensual_m') ?: 1; @endphp
                            @foreach($mesesSinContratacion['proyectos'] as $proy)
                                @php $w = $proy['mensual_m'] > 0 ? round($proy['mensual_m'] / $maxM * 100) : 0; @endphp
                                <div>
                                    <div class="flex items-baseline justify-between text-xs">
                                        <span class="font-medium text-slate-700 truncate mr-2">
                                            {{ $proy['nombre'] }}
                                            <span class="text-slate-400 ml-1">{{ $proy['cp'] }}</span>
                                        </span>
                                        <span class="text-slate-600 whitespace-nowrap">
                                            ${{ number_format($proy['monto_m'], 2) }}M · {{ $proy['plazo'] }} m{{ $proy['plazo_real'] ? '' : ' (est.)' }}
                                        </span>
                                    </div>
                                    <div class="mt-1 h-1.5 w-full rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-1.5 rounded-full {{ $sc['dot'] }} opacity-80" style="width: {{ $w }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>