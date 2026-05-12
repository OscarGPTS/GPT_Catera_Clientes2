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
                            {{ strtoupper(substr($cliente->alias_3letras ?? $cliente->razon_social, 0, 3)) }}
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
                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        <span class="font-medium text-slate-900">{{ $cliente->contactos->count() }}</span>
                        <span class="text-slate-500">contactos</span>
                    </div>
                    <span class="text-slate-200 select-none">|</span>
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
                        ['id' => 'contactos',     'label' => 'Contactos',     'count' => null],
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

                    {{-- Contactos card --}}
                    <div class="rounded-lg bg-white border border-slate-200 overflow-hidden">
                        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                            <h3 class="text-sm font-medium text-slate-900">Contactos</h3>
                            @can('update', $cliente)
                            <button type="button" wire:click="openContactoModal"
                                class="inline-flex items-center justify-center h-6 w-6 rounded-md text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            </button>
                            @endcan
                        </div>

                        @if($cliente->contactos->count() > 0)
                        <ul class="divide-y divide-slate-100">
                            @foreach($cliente->contactos->take(4) as $i => $contacto)
                            @php $bg = $avatarColors[$i % count($avatarColors)]; @endphp
                            <li class="flex items-center gap-3 px-5 py-3.5">
                                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full {{ $bg }} text-white text-xs font-semibold select-none">
                                    {{ strtoupper(substr($contacto->nombre, 0, 2)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-900 truncate">{{ $contacto->nombre }}</p>
                                    @if($contacto->puesto)
                                        <p class="text-xs text-slate-500 truncate">{{ $contacto->puesto }}</p>
                                    @endif
                                </div>
                                @if($contacto->principal)
                                    <span class="inline-flex flex-shrink-0 items-center rounded-full bg-gpt-100 px-2 py-0.5 text-xs font-medium text-gpt-800">Principal</span>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                        @if($cliente->contactos->count() > 4)
                        <div class="border-t border-slate-100 px-5 py-3 text-center">
                            <button type="button" @click="tab = 'contactos'" class="text-xs font-medium text-gpt-600 hover:text-gpt-700 transition-colors">
                                Ver todos ({{ $cliente->contactos->count() }}) →
                            </button>
                        </div>
                        @endif
                        @else
                        <div class="px-5 py-6 text-center">
                            <p class="text-sm text-slate-400">Sin contactos registrados.</p>
                        </div>
                        @endif
                    </div>

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
            <div class="rounded-lg bg-white border border-slate-200 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <div>
                        <h3 class="text-sm font-medium text-slate-900">Oportunidades comerciales</h3>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $oportunidades->count() }} oportunidad(es) en proceso</p>
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
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Referencia</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Sublinea</th>
                                <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Monto cotizado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Estado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Cierre planeado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($oportunidades as $p)
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
                            </tr>
                            @endforeach
                        </tbody>
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

        {{-- TAB: CONTACTOS --}}
        <div x-show="tab === 'contactos'" x-cloak>
            <div class="rounded-lg bg-white border border-slate-200 overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <div>
                        <h3 class="text-sm font-medium text-slate-900">Contactos del cliente</h3>
                        <p class="mt-0.5 text-xs text-slate-500">{{ $cliente->contactos->count() }} contacto(s) registrado(s)</p>
                    </div>
                    @can('update', $cliente)
                    <button type="button" wire:click="openContactoModal"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-gpt-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Agregar contacto
                    </button>
                    @endcan
                </div>

                @if($cliente->contactos->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 p-6">
                    @foreach($cliente->contactos as $i => $contacto)
                    @php $bg = $avatarColors[$i % count($avatarColors)]; @endphp
                    <div class="rounded-lg border border-slate-200 bg-white p-4 hover:border-slate-300 transition-colors">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full {{ $bg }} text-white text-sm font-semibold select-none">
                                {{ strtoupper(substr($contacto->nombre, 0, 2)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-medium text-slate-900 truncate">{{ $contacto->nombre }}</p>
                                    @if($contacto->principal)
                                        <span class="inline-flex flex-shrink-0 items-center rounded-full bg-gpt-100 px-2 py-0.5 text-xs font-medium text-gpt-800">Principal</span>
                                    @endif
                                </div>
                                @if($contacto->puesto)
                                    <p class="text-xs text-slate-500 truncate mt-0.5">{{ $contacto->puesto }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="space-y-1.5 border-t border-slate-100 pt-3">
                            @if($contacto->email)
                            <div class="flex items-center gap-2">
                                <svg class="h-3.5 w-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                <a href="mailto:{{ $contacto->email }}" class="text-xs text-gpt-600 hover:text-gpt-700 truncate">{{ $contacto->email }}</a>
                            </div>
                            @endif
                            @if($contacto->telefono)
                            <div class="flex items-center gap-2">
                                <svg class="h-3.5 w-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/></svg>
                                <span class="text-xs text-slate-600">{{ $contacto->telefono }}</span>
                            </div>
                            @endif
                            @if(!$contacto->email && !$contacto->telefono)
                            <p class="text-xs text-slate-400">Sin datos de contacto</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-6 py-14 text-center">
                    <svg class="mx-auto h-10 w-10 text-slate-200 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    <p class="text-sm font-medium text-slate-600">Sin contactos registrados</p>
                    <p class="text-xs text-slate-400 mt-1">Agrega el primer contacto para este cliente.</p>
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
                            <label for="edit_alias_3letras" class="block text-sm font-medium text-slate-700 mb-1">Alias *</label>
                            <input type="text" wire:model="edit_alias_3letras" id="edit_alias_3letras" maxlength="5"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 uppercase shadow-sm focus:border-gpt-600 focus:ring-1 focus:ring-gpt-600 @error('edit_alias_3letras') border-red-300 @enderror">
                            @error('edit_alias_3letras') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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

