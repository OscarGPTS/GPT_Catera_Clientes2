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

        {{-- Toggle: Incluir SEDENA --}}
        <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="checkbox" wire:model="incluirSedena" class="sr-only peer">
            <div class="relative h-5 w-9 rounded-full bg-slate-200 peer-checked:bg-gpt-600 transition-colors after:absolute after:start-0.5 after:top-0.5 after:h-4 after:w-4 after:rounded-full after:bg-white after:transition-all peer-checked:after:translate-x-4"></div>
            <span class="text-sm text-slate-700 whitespace-nowrap">Incluir SEDENA</span>
        </label>

        {{-- Descargar reporte mensual PDF (placeholder) --}}
        <button type="button" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm hover:bg-slate-50 transition-colors ml-auto cursor-not-allowed opacity-60" disabled title="Próximamente">
            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Reporte PDF — Próximamente
        </button>
    </div>

    {{-- SEDENA Alert --}}
    @if($concentracionSedena > 50)
        <div class="mt-4 rounded-lg border border-gpt-red-200 bg-gpt-red-50 p-4">
            <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gpt-red-100">
                    <svg class="h-5 w-5 text-gpt-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gpt-red-800">Concentración SEDENA {{ number_format($concentracionSedena, 1) }}% del pipeline — Supera el umbral de riesgo del 50%</p>
                    <p class="mt-1 text-xs text-gpt-red-600">La dependencia excesiva de un solo cliente compromete la estabilidad financiera del portafolio. Acciones recomendadas: diversificar clientes objetivo y priorizar adjudicaciones en sectores no gubernamentales.</p>
                </div>
                <a href="{{ route('oportunidades.index') }}" class="flex-shrink-0 rounded-lg border border-gpt-red-200 bg-white px-3 py-1.5 text-xs font-medium text-gpt-red-700 hover:bg-gpt-red-100 transition-colors">
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

    {{-- Section 2: Charts grid --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Chart 1: Concentración por cliente --}}
        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <h3 class="text-base font-medium text-slate-900">Concentración por cliente</h3>
            <p class="mt-1 text-sm text-slate-500">Distribución del pipeline por cliente principal</p>
            <div class="mt-4 space-y-2 max-w-[360px] mx-auto">
                @php
                    $maxCliente = max($porCliente->max() ?? 1, 1);
                    $topClientes = $porCliente->take(8);
                @endphp
                @foreach($topClientes as $nombre => $count)
                    @php
                        $pct = $pipelineTotal > 0 ? round(($count / $pipelineTotal) * 100, 1) : 0;
                        $isSedena = str_contains(strtolower($nombre), 'sedena');
                    @endphp
                    <div class="flex items-center gap-2">
                        <span class="w-24 text-xs text-slate-500 text-right truncate" title="{{ $nombre }}">{{ $nombre }}</span>
                        <div class="flex-1 h-4 rounded {{ $isSedena ? 'bg-gpt-red-100' : 'bg-slate-100' }} relative overflow-hidden">
                            <div class="absolute inset-y-0 left-0 {{ $isSedena ? 'bg-gpt-red-500' : 'bg-gpt-500' }} rounded" style="width: {{ min($pct, 100) }}%"></div>
                        </div>
                        <span class="w-14 text-xs font-medium {{ $isSedena ? 'text-gpt-red-600' : 'text-slate-600' }} text-right">{{ number_format($pct, 1) }}%</span>
                    </div>
                @endforeach
                @if($porCliente->isEmpty())
                    <p class="text-sm text-slate-400 text-center py-4">Sin datos de clientes</p>
                @endif
                @if($concentracionSedena > 50)
                    <div class="mt-3 relative">
                        <div class="absolute left-0 right-0 border-t-2 border-dashed border-gpt-red-300" style="top: 0">
                            <span class="absolute -top-3.5 right-0 text-[10px] text-gpt-red-400">Límite 50%</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

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

        {{-- Chart 4: Hit rate trimestral (placeholder) --}}
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
                </div>
            </div>
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
                    <p class="mt-0.5 text-sm text-slate-500">Snapshot financiero — Pipeline activo vs Adjudicado</p>
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
                    <p class="text-xs font-medium text-slate-500">Pipeline activo</p>
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
                    <p class="text-[11px] text-slate-400">{{ number_format($pipePct, 1) }}% del pipeline</p>
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