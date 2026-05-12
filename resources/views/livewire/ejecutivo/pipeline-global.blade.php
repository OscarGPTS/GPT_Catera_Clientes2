@php
$bands = [
    100 => ['label'=>'CONTRATADA','pct'=>'100%','rowbg'=>'bg-emerald-950/70','lbg'=>'bg-emerald-950','accent'=>'bg-emerald-500','text'=>'text-emerald-300','pctcol'=>'text-emerald-400','chip'=>'bg-emerald-900/80 border border-emerald-700/60 hover:bg-emerald-800/90 text-emerald-100','sep'=>'border-emerald-900/60','empty'=>'text-emerald-900/50'],
    75  => ['label'=>'CASI PROBABLE','pct'=>'75%','rowbg'=>'bg-cyan-950/60','lbg'=>'bg-cyan-950','accent'=>'bg-cyan-500','text'=>'text-cyan-300','pctcol'=>'text-cyan-400','chip'=>'bg-cyan-900/80 border border-cyan-700/60 hover:bg-cyan-800/90 text-cyan-100','sep'=>'border-cyan-900/60','empty'=>'text-cyan-900/50'],
    50  => ['label'=>'PROBABLE','pct'=>'50%','rowbg'=>'bg-blue-950/60','lbg'=>'bg-blue-950','accent'=>'bg-blue-500','text'=>'text-blue-300','pctcol'=>'text-blue-400','chip'=>'bg-blue-900/80 border border-blue-700/60 hover:bg-blue-800/90 text-blue-100','sep'=>'border-blue-900/60','empty'=>'text-blue-900/50'],
    25  => ['label'=>'POSIBLE','pct'=>'25%','rowbg'=>'bg-amber-950/50','lbg'=>'bg-amber-950','accent'=>'bg-amber-500','text'=>'text-amber-300','pctcol'=>'text-amber-400','chip'=>'bg-amber-900/80 border border-amber-700/60 hover:bg-amber-800/90 text-amber-100','sep'=>'border-amber-900/60','empty'=>'text-amber-900/50'],
    0   => ['label'=>'CANCELADA','pct'=>'0%','rowbg'=>'bg-red-950/40','lbg'=>'bg-red-950','accent'=>'bg-red-500','text'=>'text-red-300','pctcol'=>'text-red-400','chip'=>'bg-red-900/80 border border-red-700/60 hover:bg-red-800/90 text-red-100','sep'=>'border-red-900/60','empty'=>'text-red-900/50'],
];

$months = $statusOfertas['months'];
$proyectos = $statusOfertas['proyectos'];

$grouped = [];
foreach ($proyectos as $p) {
    foreach ($p['probs'] as $monthIdx => $prob) {
        if ($prob !== null) {
            $grouped[$prob][$monthIdx][] = $p;
        }
    }
}

$bandStats = [];
foreach (array_keys($bands) as $pond) {
    $cnt = 0; $amt = 0;
    foreach ($grouped[$pond] ?? [] as $cells) { foreach ($cells as $p) { $cnt++; $amt += $p['monto']; } }
    $bandStats[$pond] = ['count'=>$cnt,'amount'=>$amt];
}

$fmt = function(float $m): string {
    if ($m >= 1_000_000) return '$'.number_format($m/1_000_000,1).'M';
    if ($m >= 10_000)    return '$'.number_format($m/1_000,0).'k';
    return '$'.number_format($m,0);
};

$YCOL = 176; $XCOL = 104;
@endphp

<div class="flex flex-col h-full bg-slate-950 text-slate-100">

    {{-- HEADER --}}
    <div class="shrink-0 flex items-center justify-between gap-4 px-5 h-12 bg-slate-900 border-b border-slate-800">
        <div class="flex items-center gap-3">
            <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-gradient-to-br from-gpt-500 to-gpt-700 shrink-0">
                <svg class="h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                </svg>
            </div>
            <div>
                <span class="text-sm font-semibold text-white">Pipeline Global &middot; GPT Services 2026</span>
                <span class="ml-2 text-[11px] text-slate-500">{{ $kpis['count'] }} ofertas &middot; Cartera total ${{ number_format($kpis['total']/1_000_000,1) }}M &middot; Ponderado esperado ${{ number_format($kpis['ponderado']/1_000_000,2) }}M USD</span>
            </div>
        </div>
        <div class="flex items-center gap-1.5">
            @foreach([100=>['bg-emerald-500','100%'],75=>['bg-cyan-500','75%'],50=>['bg-blue-500','50%'],25=>['bg-amber-500','25%'],0=>['bg-red-500','0%']] as $pv=>$li)
            <span class="flex items-center gap-1 px-2 py-0.5 rounded-full bg-slate-800 text-[9px] text-slate-400">
                <span class="inline-block w-1.5 h-1.5 rounded-full {{ $li[0] }}"></span>{{ $li[1] }}
            </span>
            @endforeach
        </div>
    </div>

    {{-- SWIMLANE MATRIX --}}
    <div class="flex-1 overflow-auto bg-slate-950">
        <div style="min-width: {{ $YCOL + count($months) * $XCOL }}px">

            {{-- Month header sticky --}}
            <div class="flex sticky top-0 z-20 bg-slate-900 border-b border-slate-700">
                <div class="shrink-0 sticky left-0 z-30 bg-slate-900 border-r border-slate-700 flex flex-col items-center justify-center"
                     style="width:{{ $YCOL }}px; height:34px">
                    <span class="text-[9px] text-slate-600 uppercase tracking-widest">Probabilidad / Mes</span>
                </div>
                @foreach($months as $idx => $m)
                <div class="shrink-0 flex items-center justify-center text-[11px] font-medium border-r border-slate-800"
                     style="width:{{ $XCOL }}px; height:34px">
                    <span class="text-slate-500">{{ $m['label'] }}</span>
                </div>
                @endforeach
            </div>

            {{-- Band rows --}}
            @foreach($bands as $pond => $band)
            @php
                $stats = $bandStats[$pond];
                $maxInCol = 0;
                foreach ($grouped[$pond] ?? [] as $col) { $maxInCol = max($maxInCol, count($col)); }
                $minH = max(72, $maxInCol * 60 + 16);
            @endphp
            <div class="flex border-b {{ $band['sep'] }} {{ $band['rowbg'] }}" style="min-height:{{ $minH }}px">
                {{-- Y-axis label sticky --}}
                <div class="shrink-0 sticky left-0 z-10 {{ $band['lbg'] }} border-r border-slate-700 flex flex-col justify-center px-4 py-3 gap-0.5"
                     style="width:{{ $YCOL }}px">
                    <div class="flex items-center gap-2 mb-0.5">
                        <span class="w-2.5 h-2.5 rounded-full {{ $band['accent'] }} shrink-0"></span>
                        <span class="text-[10px] font-semibold uppercase tracking-wider {{ $band['text'] }}">{{ $band['label'] }}</span>
                    </div>
                    <span class="text-2xl font-black leading-none ml-5 {{ $band['pctcol'] }}">{{ $band['pct'] }}</span>
                    <span class="text-[10px] ml-5 mt-0.5 leading-none {{ $stats['count']>0 ? 'text-slate-400' : $band['empty'] }}">
                        @if($stats['count']>0){{ $stats['count'] }} oferta{{ $stats['count']>1?'s':'' }} &middot; {{ $fmt($stats['amount']) }}
                        @else Sin ofertas @endif
                    </span>
                </div>
                {{-- Cells --}}
                @foreach($months as $idx => $m)
                @php $cells = $grouped[$pond][$idx] ?? []; @endphp
                <div class="shrink-0 border-r border-slate-800/50 p-1.5 flex flex-col gap-1"
                     style="width:{{ $XCOL }}px">
                    @foreach($cells as $p)
                    <div title="{{ $p['nombre'] }}&#10;CP: {{ $p['cp'] }}&#10;Resp: {{ $p['resp'] }}&#10;Monto: ${{ number_format($p['monto'],2) }} USD&#10;Prob: {{ $p['probs'][$idx] }}%"
                         class="block rounded px-1.5 py-1.5 transition-colors duration-150 {{ $band['chip'] }}">
                        <div class="flex items-center justify-between gap-0.5 mb-0.5">
                            <span class="text-[9px] font-bold leading-none truncate flex-1">{{ $p['cp'] }}</span>
                            <span class="text-[8px] font-mono bg-white/10 px-0.5 rounded shrink-0 leading-none opacity-80">{{ $p['sublinea'] }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-0.5">
                            <span class="text-[9px] opacity-60 truncate flex-1 leading-none">{{ $p['alias'] }}</span>
                            <span class="text-[9px] font-semibold shrink-0 leading-none">{{ $fmt($p['monto']) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
            @endforeach

            {{-- Footer: totals per column --}}
            <div class="flex bg-slate-900/90 border-t border-slate-700">
                <div class="shrink-0 sticky left-0 z-10 bg-slate-900 border-r border-slate-700 flex items-center px-4"
                     style="width:{{ $YCOL }}px; height:30px">
                    <span class="text-[9px] text-slate-600 uppercase tracking-wider">Total por mes</span>
                </div>
                @foreach($months as $idx => $m)
                @php
                    $colAmt=0; $colCnt=0;
                    foreach(array_keys($bands) as $pond){ foreach($grouped[$pond][$idx]??[] as $p){ $colAmt+=$p['monto']; $colCnt++; } }
                @endphp
                <div class="shrink-0 flex flex-col items-center justify-center border-r border-slate-800 gap-px"
                     style="width:{{ $XCOL }}px; height:30px">
                    @if($colCnt>0)
                    <span class="text-[9px] font-semibold leading-none text-slate-400">{{ $fmt($colAmt) }}</span>
                    <span class="text-[8px] text-slate-600 leading-none">{{ $colCnt }} of.</span>
                    @endif
                </div>
                @endforeach
            </div>

        </div>
    </div>
</div>