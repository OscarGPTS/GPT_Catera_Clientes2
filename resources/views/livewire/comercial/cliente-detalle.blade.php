<div>
    @section('title', $cliente->razon_social)

    @php
        $avatarColors = ['bg-blue-500', 'bg-purple-500', 'bg-emerald-500', 'bg-amber-500', 'bg-gpt-600', 'bg-teal-500', 'bg-rose-500'];

        $facturadoFmt = $facturadoTotal >= 1_000_000
            ? '$' . number_format($facturadoTotal / 1_000_000, 1) . 'M'
            : ($facturadoTotal >= 1_000 ? '$' . number_format($facturadoTotal / 1_000, 0) . 'k' : '$' . number_format($facturadoTotal, 0));

        $pipelineFmt = $pipelineActivo >= 1_000_000
            ? '$' . number_format($pipelineActivo / 1_000_000, 1) . 'M'
            : ($pipelineActivo >= 1_000 ? '$' . number_format($pipelineActivo / 1_000, 0) . 'k' : '$' . number_format($pipelineActivo, 0));

        $segmentoBadge = match($cliente->segmento) {
            'A' => ['bg' => 'bg-gpt-100 text-gpt-800', 'label' => 'Cliente A'],
            'B' => ['bg' => 'bg-blue-100 text-blue-800', 'label' => 'Cliente B'],
            'C' => ['bg' => 'bg-slate-100 text-slate-600', 'label' => 'Cliente C'],
            default => null,
        };

        $scoreCreditoBadge = match($cliente->segmento) {
            'A' => ['bg' => 'bg-green-100 text-green-800', 'label' => 'A — bajo riesgo'],
            'B' => ['bg' => 'bg-amber-100 text-amber-800', 'label' => 'B — riesgo medio'],
            'C' => ['bg' => 'bg-gpt-red-100 text-gpt-red-800', 'label' => 'C — riesgo alto'],
            default => ['bg' => 'bg-slate-100 text-slate-600', 'label' => 'Sin clasificar'],
        };
    @endphp

    {{-- Alpine scope: tabs --}}
    <div x-data="{ tab: 'resumen' }">

        {{-- Header card --}}
        <div class="rounded-lg bg-white border border-slate-200 overflow-hidden mb-6">

            <div class="px-6 pt-5 pb-4">
                {{-- Breadcrumb --}}
                <nav class="flex items-center gap-1.5 text-sm text-slate-500 mb-4">
                    <a href="{{ route('clientes.index') }}" class="hover:text-slate-700 transition-colors">Clientes</a>
                    <svg class="h-4 w-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    <span class="text-slate-900 font-medium truncate">{{ $cliente->razon_social }}</span>
                </nav>

                {{-- Identity + Actions row --}}
                <div class="flex items-start gap-4 justify-between">
                    <div class="flex items-center gap-4 min-w-0">
                        {{-- Avatar --}}
                        <div class="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-xl bg-gpt-600 text-white text-base font-semibold select-none shadow-sm">
                            {{ strtoupper(substr($cliente->alias ?? $cliente->razon_social, 0, 3)) }}
                        </div>

                        <div class="min-w-0">
                            {{-- Name + badges --}}
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <h1 class="text-xl font-medium text-slate-900 truncate">{{ $cliente->razon_social }}</h1>

                                @if($cliente->activo)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 flex-shrink-0">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-600 flex-shrink-0"></span>
                                        Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500 flex-shrink-0">Inactivo</span>
                                @endif

                                @if($segmentoBadge)
                                    <span class="inline-flex items-center rounded-full {{ $segmentoBadge['bg'] }} px-2 py-0.5 text-xs font-medium flex-shrink-0">{{ $segmentoBadge['label'] }}</span>
                                @endif
                            </div>

                            {{-- Meta --}}
                            <p class="text-sm text-slate-500 flex flex-wrap items-center gap-x-1.5 gap-y-0.5">
                                @if($cliente->rfc)
                                    <span>RFC: <span class="font-mono text-slate-700">{{ $cliente->rfc }}</span></span>
                                    <span class="text-slate-300">·</span>
                                @endif
                                @if($cliente->sector)
                                    <span>{{ $cliente->sector }}</span>
                                    <span class="text-slate-300">·</span>
                                @endif
                                <span>Cliente desde {{ $cliente->created_at->translatedFormat('M Y') }}</span>
                            </p>
                        </div>
                    </div>

                    {{-- Action buttons --}}
                    <div class="flex items-center gap-2 flex-shrink-0 pt-0.5">
                        @can('update', $cliente)
                        <button type="button" wire:click="openEditModal"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors shadow-sm">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                            Editar
                        </button>
                        @endcan
                        @can('crear oportunidad')
                        <a href="{{ route('oportunidades.create') }}"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-gpt-700 transition-colors shadow-sm">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Nueva oportunidad
                        </a>
                        @endcan
                    </div>
                </div>

                {{-- Quick stats bar --}}
                <div class="mt-4 pt-4 border-t border-slate-100 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm">
                    <div class="flex items-center gap-1.5 text-slate-600">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                        <span class="font-medium text-slate-900">{{ $proyectos->count() }}</span>
                        <span class="text-slate-500">proyectos</span>
                    </div>
                    <span class="text-slate-200 select-none">|</span>
                    <div class="flex items-center gap-1.5 text-slate-600">
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span class="text-slate-500">Última actividad:</span>
                        <span class="font-medium text-slate-900">{{ $cliente->updated_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>

            {{-- Tab navigation --}}
            <div class="flex overflow-x-auto px-6 border-t border-slate-100 -mb-px">
                @php
                    $tabItems = [
                        ['id' => 'resumen',       'label' => 'Resumen',       'count' => null],
                        ['id' => 'oportunidades', 'label' => 'Oportunidades', 'count' => $oportunidades->count()],
                        ['id' => 'proyectos',     'label' => 'Proyectos',     'count' => $proyectos->count()],
                        ['id' => 'facturacion',   'label' => 'Facturación',   'count' => null],
                        ['id' => 'documentos',    'label' => 'Documentos',    'count' => null],
                    ];
                @endphp
                @foreach($tabItems as $t)
                <button type="button"
                    @click="tab = '{{ $t['id'] }}'"
                    :class="tab === '{{ $t['id'] }}'
                        ? 'border-b-2 border-gpt-600 text-gpt-600'
                        : 'border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                    class="inline-flex items-center gap-1.5 whitespace-nowrap py-3 pr-5 text-sm font-medium transition-colors focus:outline-none">
                    {{ $t['label'] }}
                    @if($t['count'] !== null)
                        <span :class="tab === '{{ $t['id'] }}' ? 'bg-gpt-100 text-gpt-800' : 'bg-slate-100 text-slate-600'"
                            class="inline-flex items-center justify-center rounded-full px-1.5 py-0.5 text-xs font-medium leading-none transition-colors">
                            {{ $t['count'] }}
                        </span>
                    @endif
                </button>
                @endforeach
            </div>
        </div>

        {{-- Notifications --}}
        @if($successMessage)
            <div wire:transition.opacity.duration.500ms class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">{{ $successMessage }}</div>
        @endif
        @if($errorMessage)
            <div wire:transition.opacity.duration.500ms class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ $errorMessage }}</div>
        @endif

        {{-- TAB: RESUMEN --}}
        <div x-show="tab === 'resumen'" x-cloak>

            {{-- KPI row --}}
            <div class="grid grid-cols-2 gap-4 xl:grid-cols-4 mb-6">
                <div class="rounded-lg bg-white border border-slate-200 p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 mb-1">Facturado total</p>
                    <p class="text-3xl font-medium text-slate-900">{{ $facturadoFmt }}</p>
                    <p class="mt-1 text-xs text-slate-400">Acumulado histórico</p>
                </div>
                <div class="rounded-lg bg-white border border-slate-200 p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 mb-1">Pipeline activo</p>
                    <p class="text-3xl font-medium text-slate-900">{{ $pipelineFmt }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $oportunidades->whereIn('estado', ['cotizando','cotizado','presentado'])->count() }} oportunidades abiertas</p>
                </div>
                <div class="rounded-lg bg-white border border-slate-200 p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 mb-1">Oportunidades activas</p>
                    <p class="text-3xl font-medium text-slate-900">{{ $oportunidades->count() }}</p>
                    <p class="mt-1 text-xs text-slate-400">En proceso comercial</p>
                </div>
                <div class="rounded-lg bg-white border border-slate-200 p-5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 mb-1">Proyectos totales</p>
                    <p class="text-3xl font-medium text-slate-900">{{ $proyectos->count() }}</p>
                    <p class="mt-1 text-xs text-slate-400">Histórico completo</p>
                </div>
            </div>

            {{-- 2-column content --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                {{-- Left: Proyectos históricos table --}}
                <div class="lg:col-span-2">
                    <div class="rounded-lg bg-white border border-slate-200 overflow-hidden">
                        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                            <h3 class="text-sm font-medium text-slate-900">Proyectos históricos</h3>
                            <button type="button" @click="tab = 'proyectos'" class="text-sm font-medium text-gpt-600 hover:text-gpt-700 transition-colors">
                                Ver todos →
                            </button>
                        </div>

                        @if($proyectos->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Proyecto</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Tipo</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Monto</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Estado</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Cierre</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach($proyectos->take(8) as $p)
                                    @php
                                        $montoP = $p->cotizaciones->max('precio_venta_final') ?? 0;
                                        $montoPFmt = $montoP >= 1_000_000
                                            ? '$' . number_format($montoP / 1_000_000, 1) . 'M'
                                            : ($montoP >= 1_000 ? '$' . number_format($montoP / 1_000, 0) . 'k' : ($montoP > 0 ? '$' . number_format($montoP, 0) : '—'));
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-5 py-3.5">
                                            <a href="{{ route('oportunidades.show', $p) }}" class="group">
                                                <span class="block text-sm font-medium font-mono text-slate-900 group-hover:text-gpt-600 transition-colors">{{ $p->cp_numero ?? $p->dn_numero ?? '—' }}</span>
                                                @if($p->tech_reference)
                                                    <span class="block text-xs text-slate-400 mt-0.5 truncate max-w-[200px]">{{ $p->tech_reference }}</span>
                                                @endif
                                            </a>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            @if($p->sublinea)
                                                <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">{{ $p->sublinea->nombre }}</span>
                                            @else
                                                <span class="text-xs text-slate-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-right">
                                            <span class="text-sm font-medium text-slate-900">{{ $montoPFmt }}</span>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <x-badge :status="$p->estado" />
                                        </td>
                                        <td class="px-4 py-3.5 whitespace-nowrap">
                                            <span class="text-sm text-slate-600">{{ $p->fecha_fin_planeada?->translatedFormat('M Y') ?? '—' }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="px-6 py-12 text-center">
                            <svg class="mx-auto h-8 w-8 text-slate-300 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                            <p class="text-sm text-slate-500">Sin proyectos registrados.</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Right sidebar --}}
                <div class="space-y-4">

                    {{-- Condiciones comerciales --}}
                    <div class="rounded-lg bg-white border border-slate-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100">
                            <h3 class="text-sm font-medium text-slate-900">Condiciones comerciales</h3>
                        </div>
                        <dl class="divide-y divide-slate-100">
                            <div class="flex items-center justify-between px-5 py-3">
                                <dt class="text-sm text-slate-500">Sector</dt>
                                <dd class="text-sm font-medium text-slate-900">{{ $cliente->sector ?? '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between px-5 py-3">
                                <dt class="text-sm text-slate-500">Segmento</dt>
                                <dd class="text-sm font-medium text-slate-900">
                                    @if($cliente->segmento)
                                        {{ $cliente->segmento }} — {{ match($cliente->segmento) { 'A' => 'Estratégico', 'B' => 'Crecimiento', 'C' => 'Operativo', default => '' } }}
                                    @else
                                        —
                                    @endif
                                </dd>
                            </div>
                            <div class="flex items-center justify-between px-5 py-3">
                                <dt class="text-sm text-slate-500">Score crédito</dt>
                                <dd>
                                    <span class="inline-flex items-center rounded-full {{ $scoreCreditoBadge['bg'] }} px-2.5 py-0.5 text-xs font-medium">
                                        {{ $scoreCreditoBadge['label'] }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between px-5 py-3">
                                <dt class="text-sm text-slate-500">RFC</dt>
                                <dd class="text-sm font-mono text-slate-900">{{ $cliente->rfc ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>

                </div>
            </div>
        </div>

        {{-- TAB: OPORTUNIDADES --}}
        <div x-show="tab === 'oportunidades'" x-cloak>
            <div class="rounded-lg border border-slate-200 bg-white overflow-hidden"
                 x-data="{
                     init() {
                         const top = this.$refs.topBar;
                         const body = this.$refs.tableWrap;
                         top.addEventListener('scroll', () => { body.scrollLeft = top.scrollLeft; });
                         body.addEventListener('scroll', () => { top.scrollLeft = body.scrollLeft; });
                     }
                 }">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <div>
                        <h3 class="text-sm font-medium text-slate-900">Oportunidades comerciales</h3>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $oportunidades->count() }} oportunidad(es) en pipeline</p>
                    </div>
                    @can('crear oportunidad')
                    <a href="{{ route('oportunidades.create') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-gpt-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Nueva oportunidad
                    </a>
                    @endcan
                </div>

                @if($oportunidades->count() > 0)
                {{-- Top scrollbar mirror --}}
                <div x-ref="topBar" class="overflow-x-scroll border-b border-slate-100" style="height:10px">
                    <div style="min-width:1922px;height:1px"></div>
                </div>
                <div x-ref="tableWrap" class="overflow-x-auto">
                    {{-- cols: CP 90 | Contacto 130 | Datos 140 | Lugar 110 | Alcance 210 | Oferta 170 | FEnvío 100 | FModif 100 | Emitidas 120 | Hitos 140 | Resp 120 | Status 100 | Arch 110 | ConcAdj 150 | %Adj 90 | %Real 70 | Cartera 120 | Acc 52 = 1922 --}}
                    <table class="w-full table-fixed divide-y divide-slate-200" style="min-width:1922px">
                        <colgroup>
                            <col style="width:90px">
                            <col style="width:130px">
                            <col style="width:140px">
                            <col style="width:110px">
                            <col style="width:210px">
                            <col style="width:170px">
                            <col style="width:100px">
                            <col style="width:100px">
                            <col style="width:120px">
                            <col style="width:140px">
                            <col style="width:120px">
                            <col style="width:100px">
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
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sticky left-[90px] z-20 bg-slate-50">Contacto</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sticky left-[220px] z-20 bg-slate-50">Datos de Contacto</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 sticky left-[360px] z-20 bg-slate-50 border-r border-slate-300">Lugar</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Alcance</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Oferta</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fecha Envío</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fecha Modif.</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Ofertas Emitidas</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Hitos de Pago</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Responsable</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Arch. Oferta</th>
                                <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Concepto Adj.</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">% Adj.</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">% Real</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Cartera Esp.</th>
                                <th class="px-3 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500"></th>
                            </tr>
                        </thead>

                        @foreach($oportunidades as $op)
                        @php
                            $fechaEnvio = $op->fecha_envio
                                ?? $op->cotizaciones->where('fecha_emision', '!=', null)->sortByDesc('version')->first()?->fecha_emision;
                            $fechaModif = $op->fecha_modificacion_oferta;
                            $ponderacionLabel = match((int) $op->ponderacion) {
                                10  => 'Remoto',
                                25  => 'Posible',
                                50  => 'Probable',
                                75  => 'Casi Probable',
                                100 => 'Contratado',
                                default => $op->ponderacion . '%',
                            };
                            $responsableNombre = $op->elaboro?->name ?? $op->gerenteProyectos?->name ?? '—';
                            $incompleto = ! $fechaEnvio;
                        @endphp
                        <tbody x-data="{ expanded: false }" class="divide-y divide-slate-100">
                            {{-- Main row --}}
                            <tr class="bg-white hover:bg-slate-50/60 transition-colors cursor-pointer select-none group {{ $incompleto ? 'border-l-2 border-l-amber-400' : '' }}"
                                @click="expanded = !expanded">

                                {{-- CP + toggle --}}
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

                                {{-- Contacto --}}
                                <td class="px-3 py-3 text-sm text-slate-600 sticky left-[90px] z-10 bg-white group-hover:bg-slate-50/60 overflow-hidden">
                                    <div class="truncate">{{ $op->contacto ?? '—' }}</div>
                                </td>

                                {{-- Datos de Contacto --}}
                                <td class="px-3 py-3 text-sm text-slate-600 sticky left-[220px] z-10 bg-white group-hover:bg-slate-50/60 overflow-hidden">
                                    <div class="truncate">{{ $op->datos_contacto ?? '—' }}</div>
                                </td>

                                {{-- Lugar --}}
                                <td class="px-3 py-3 text-sm text-slate-600 sticky left-[360px] z-10 bg-white group-hover:bg-slate-50/60 border-r border-slate-200 overflow-hidden">
                                    <div class="truncate">{{ $op->lugar?->nombre ?? '—' }}</div>
                                </td>

                                {{-- Alcance --}}
                                <td class="px-3 py-3 text-sm text-slate-600 overflow-hidden">
                                    <div class="truncate">{{ $op->alcance ?? '—' }}</div>
                                </td>

                                {{-- Oferta / tech_reference --}}
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

                                {{-- % Adj. --}}
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
                                                <button type="submit" class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    Cancelar
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            {{-- Expandable detail panel --}}
                            <tr x-show="expanded" x-cloak
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100 translate-y-0"
                                x-transition:leave-end="opacity-0 -translate-y-1">
                                <td colspan="18" class="bg-slate-50/80 px-6 py-4 border-b border-slate-200">
                                    <div class="mb-3 flex items-center justify-between">
                                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Detalle — {{ $op->cp_numero ?? 'sin CP' }}</span>
                                        <button @click="expanded = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-2 gap-x-8 gap-y-3 sm:grid-cols-3 lg:grid-cols-4">
                                        <div>
                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Contacto</dt>
                                            <dd class="mt-0.5 text-sm text-slate-800 break-words">{{ $op->contacto ?? '—' }}</dd>
                                        </div>
                                        <div class="col-span-2">
                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Datos de Contacto</dt>
                                            <dd class="mt-0.5 text-sm text-slate-800 break-words whitespace-pre-wrap">{{ $op->datos_contacto ?? '—' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Lugar</dt>
                                            <dd class="mt-0.5 text-sm text-slate-800">{{ $op->lugar?->nombre ?? '—' }}</dd>
                                        </div>
                                        <div class="col-span-3">
                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Alcance</dt>
                                            <dd class="mt-0.5 text-sm text-slate-800 break-words whitespace-pre-wrap">{{ $op->alcance ?? '—' }}</dd>
                                        </div>
                                        <div class="col-span-2">
                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Oferta / Tech. Ref.</dt>
                                            <dd class="mt-0.5 text-sm text-slate-800 break-words">{{ $op->tech_reference ?? '—' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Fecha Envío</dt>
                                            <dd class="mt-0.5 text-sm {{ $fechaEnvio ? 'text-slate-800' : 'text-amber-600 font-medium' }}">
                                                {{ $fechaEnvio?->format('d/m/Y') ?? 'Sin fecha' }}
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Fecha Modif. Oferta</dt>
                                            <dd class="mt-0.5 text-sm text-slate-800">{{ $fechaModif?->format('d/m/Y') ?? '—' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Ofertas Emitidas</dt>
                                            <dd class="mt-0.5 text-sm font-semibold text-slate-900">
                                                {{ $op->monto_usd ? '$ ' . number_format($op->monto_usd, 2, '.', ',') : '—' }}
                                            </dd>
                                        </div>
                                        <div class="col-span-3">
                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Hitos de Pago</dt>
                                            <dd class="mt-0.5 text-sm text-slate-800 break-words whitespace-pre-wrap">{{ $op->hitos_pago ?? '—' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Responsable</dt>
                                            <dd class="mt-0.5 text-sm text-slate-800">{{ $responsableNombre }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Status</dt>
                                            <dd class="mt-0.5"><x-badge :status="$op->estado" /></dd>
                                        </div>
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
                                        <div class="col-span-2">
                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Concepto Adj.</dt>
                                            <dd class="mt-0.5 text-sm text-slate-800 break-words whitespace-pre-wrap">{{ $op->concepto_adjudicacion ?? '—' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">% Adjudicación</dt>
                                            <dd class="mt-1">
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                                    {{ $op->ponderacion >= 75 ? 'bg-green-100 text-green-800' : ($op->ponderacion >= 50 ? 'bg-amber-100 text-amber-800' : ($op->ponderacion >= 25 ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700')) }}">
                                                    {{ $op->ponderacion }}% — {{ $ponderacionLabel }}
                                                </span>
                                            </dd>
                                        </div>
                                        <div>
                                            <dt class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">% Real</dt>
                                            <dd class="mt-0.5 text-sm font-semibold text-slate-900">
                                                {{ $op->porcentaje_adjudicacion ? number_format($op->porcentaje_adjudicacion, 1) . '%' : '—' }}
                                            </dd>
                                        </div>
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
                        @endforeach
                    </table>
                </div>
                @else
                <div class="px-6 py-14 text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-200 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                    <p class="text-sm font-medium text-slate-600">Sin oportunidades activas</p>
                    <p class="text-xs text-slate-400 mt-1">Las oportunidades en proceso comercial aparecerán aquí.</p>
                </div>
                @endif
            </div>
        </div>

        {{-- TAB: PROYECTOS --}}
        <div x-show="tab === 'proyectos'" x-cloak>
            <div class="rounded-lg bg-white border border-slate-200 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <div>
                        <h3 class="text-sm font-medium text-slate-900">Todos los proyectos</h3>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $proyectos->count() }} proyecto(s) históricos</p>
                    </div>
                </div>

                @if($proyectos->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Referencia</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Sublinea</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Monto</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Estado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Cierre planeado</th>
                                <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-slate-500">Año</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($proyectos as $p)
                            @php
                                $monto = $p->cotizaciones->max('precio_venta_final') ?? 0;
                                $montoFmt = $monto >= 1_000_000
                                    ? '$' . number_format($monto / 1_000_000, 1) . 'M'
                                    : ($monto >= 1_000 ? '$' . number_format($monto / 1_000, 0) . 'k' : ($monto > 0 ? '$' . number_format($monto, 0) : '—'));
                            @endphp
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('oportunidades.show', $p) }}" class="group">
                                        <span class="block text-sm font-medium font-mono text-slate-900 group-hover:text-gpt-600 transition-colors">{{ $p->cp_numero ?? $p->dn_numero ?? '—' }}</span>
                                        @if($p->tech_reference)
                                            <span class="block text-xs text-slate-400 mt-0.5 truncate max-w-[220px]">{{ $p->tech_reference }}</span>
                                        @endif
                                    </a>
                                </td>
                                <td class="px-4 py-3.5">
                                    @if($p->sublinea)
                                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">{{ $p->sublinea->nombre }}</span>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <span class="text-sm font-medium text-slate-900">{{ $montoFmt }}</span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <x-badge :status="$p->estado" />
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="text-sm text-slate-600">{{ $p->fecha_fin_planeada?->translatedFormat('d M Y') ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="text-sm text-slate-500">{{ $p->anio ?? '—' }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="px-6 py-14 text-center">
                    <p class="text-sm text-slate-500">Sin proyectos registrados.</p>
                </div>
                @endif
            </div>
        </div>

        {{-- TAB: FACTURACIÓN --}}
        <div x-show="tab === 'facturacion'" x-cloak>
            <div class="rounded-lg bg-white border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-sm font-medium text-slate-900">Facturación</h3>
                </div>
                <div class="px-6 py-14 text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-200 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/></svg>
                    <p class="text-sm font-medium text-slate-600">Módulo en desarrollo</p>
                    <p class="text-xs text-slate-400 mt-1">El historial de facturación estará disponible próximamente.</p>
                </div>
            </div>
        </div>

        {{-- TAB: DOCUMENTOS --}}
        <div x-show="tab === 'documentos'" x-cloak>
            <div class="rounded-lg bg-white border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-sm font-medium text-slate-900">Documentos</h3>
                </div>
                <div class="px-6 py-14 text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-200 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    <p class="text-sm font-medium text-slate-600">Módulo en desarrollo</p>
                    <p class="text-xs text-slate-400 mt-1">Los documentos del cliente estarán disponibles próximamente.</p>
                </div>
            </div>
        </div>

    </div>{{-- end Alpine scope --}}

    {{-- Modal: Agregar contacto --}}
    @if($showContactoModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50" wire:click.self="closeContactoModal">
        <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-medium text-slate-900">Agregar contacto</h3>
                <button type="button" wire:click="closeContactoModal" class="rounded-md p-1 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit.prevent="addContacto">
                <div class="space-y-4">
                    <div>
                        <label for="contacto_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre *</label>
                        <input type="text" wire:model="contacto_nombre" id="contacto_nombre"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-1 focus:ring-gpt-600 @error('contacto_nombre') border-red-300 @enderror">
                        @error('contacto_nombre') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="contacto_puesto" class="block text-sm font-medium text-slate-700 mb-1">Puesto</label>
                        <input type="text" wire:model="contacto_puesto" id="contacto_puesto"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-1 focus:ring-gpt-600">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="contacto_email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                            <input type="email" wire:model="contacto_email" id="contacto_email"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-1 focus:ring-gpt-600 @error('contacto_email') border-red-300 @enderror">
                            @error('contacto_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="contacto_telefono" class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                            <input type="text" wire:model="contacto_telefono" id="contacto_telefono"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-1 focus:ring-gpt-600">
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" wire:model="contacto_principal" class="h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                        <span class="text-sm text-slate-700">Marcar como contacto principal</span>
                    </label>
                </div>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" wire:click="closeContactoModal"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">Cancelar</button>
                    <button type="submit"
                        class="rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-700 transition-colors">Agregar contacto</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    {{-- Modal: Editar cliente --}}
    @if($showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50" wire:click.self="closeEditModal">
        <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-medium text-slate-900">Editar cliente</h3>
                <button type="button" wire:click="closeEditModal" class="rounded-md p-1 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form wire:submit.prevent="updateCliente">
                <div class="space-y-4">
                    <div>
                        <label for="edit_razon_social" class="block text-sm font-medium text-slate-700 mb-1">Razón social *</label>
                        <input type="text" wire:model="edit_razon_social" id="edit_razon_social"
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-1 focus:ring-gpt-600 @error('edit_razon_social') border-red-300 @enderror">
                        @error('edit_razon_social') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_alias" class="block text-sm font-medium text-slate-700 mb-1">Alias *</label>
                            <input type="text" wire:model="edit_alias" id="edit_alias" maxlength="30"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 uppercase shadow-sm focus:border-gpt-600 focus:ring-1 focus:ring-gpt-600 @error('edit_alias') border-red-300 @enderror">
                            @error('edit_alias') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="edit_rfc" class="block text-sm font-medium text-slate-700 mb-1">RFC</label>
                            <input type="text" wire:model="edit_rfc" id="edit_rfc" maxlength="13"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-mono text-slate-900 uppercase shadow-sm focus:border-gpt-600 focus:ring-1 focus:ring-gpt-600 @error('edit_rfc') border-red-300 @enderror">
                            @error('edit_rfc') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="edit_sector" class="block text-sm font-medium text-slate-700 mb-1">Sector</label>
                            <select wire:model="edit_sector" id="edit_sector"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-1 focus:ring-gpt-600">
                                <option value="">Seleccionar...</option>
                                <option value="Gobierno">Gobierno</option>
                                <option value="Energia">Energía</option>
                                <option value="Industrial">Industrial</option>
                                <option value="Privado">Privado</option>
                            </select>
                        </div>
                        <div>
                            <label for="edit_segmento" class="block text-sm font-medium text-slate-700 mb-1">Segmento</label>
                            <select wire:model="edit_segmento" id="edit_segmento"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-1 focus:ring-gpt-600">
                                <option value="">Seleccionar...</option>
                                <option value="A">A — Estratégico</option>
                                <option value="B">B — Crecimiento</option>
                                <option value="C">C — Operativo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" wire:click="closeEditModal"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">Cancelar</button>
                    <button type="submit"
                        class="rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-700 transition-colors">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>

