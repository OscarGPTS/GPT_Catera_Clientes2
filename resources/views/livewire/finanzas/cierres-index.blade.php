<div>
    @section('title', 'Cierres Mensuales')

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl sm:text-2xl font-medium text-slate-900">Cierres Mensuales</h2>
                <p class="mt-1 text-sm text-slate-500">Comparativa SAT vs Gerencial · Cierre mensual D1</p>
            </div>
        </div>

        @if($successMessage)
            <div wire:transition.opacity.duration.500ms class="rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800">{{ $successMessage }}</div>
        @endif

        @if($errorMessage)
            <div wire:transition.opacity.duration.500ms class="rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-800">{{ $errorMessage }}</div>
        @endif

        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="cierre-anio" class="block text-sm font-medium text-slate-700">Año</label>
                    <select id="cierre-anio" wire:model.live="anio" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                        @foreach($aniosDisponibles as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="cierre-mes" class="block text-sm font-medium text-slate-700">Mes</label>
                    <select id="cierre-mes" wire:model.live="mes" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                        @foreach($meses as $num => $nombre)
                            <option value="{{ $num }}">{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                @can('generar cierre gerencial')
                <button type="button" wire:click="generarCierre" wire:loading.attr="disabled" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span wire:loading.remove wire:target="generarCierre">Generar cierre del mes</span>
                    <span wire:loading wire:target="generarCierre">Generando...</span>
                    <span class="rounded-full bg-white/20 px-1.5 py-0.5 text-xs">~5 segundos</span>
                </button>
                @endcan
                @unlessrole('invitado')
                <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Exportar PDF
                </button>
                @endunlessrole
            </div>

            @if($generando)
            <div class="mt-4 space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600">{{ $progresoTexto }}</span>
                    <span class="text-slate-400">{{ $progresoEtapa }}/3</span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-gpt-600 transition-all duration-700" style="width: {{ $progresoPorcentaje }}%"></div>
                </div>
            </div>
            @endif
        </div>

        <div class="flex flex-col gap-6 lg:flex-row">
            <div class="flex-1 min-w-0 space-y-6">
                <div class="flex overflow-x-auto border-b border-slate-200">
                    <button wire:click="$set('tab', 'sat')" class="whitespace-nowrap border-b-2 px-4 py-2.5 text-sm font-medium transition-colors {{ $tab === 'sat' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">Cierre SAT</button>
                    <button wire:click="$set('tab', 'gerencial')" class="whitespace-nowrap border-b-2 px-4 py-2.5 text-sm font-medium transition-colors {{ $tab === 'gerencial' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">Cierre Gerencial</button>
                    <button wire:click="$set('tab', 'comparativa')" class="whitespace-nowrap border-b-2 px-4 py-2.5 text-sm font-medium transition-colors {{ $tab === 'comparativa' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">Comparativa</button>
                </div>

                @if($tab === 'sat')
                <div class="space-y-6">
                    <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
                        <div class="bg-slate-100 px-6 py-4">
                            <h3 class="text-base font-medium text-slate-900">SAT base</h3>
                            <p class="mt-0.5 text-sm text-slate-500">Facturas registradas en el SAT para el período seleccionado</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Folio</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Cliente</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Proyecto DN</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fecha</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Monto USD</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse($facturasSat as $factura)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $factura['folio'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ $factura['cliente'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ $factura['proyecto_dn'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ $factura['fecha'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-right text-sm font-medium text-slate-900">$ {{ number_format($factura['monto_usd'] ?? 0, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-12 text-center text-sm text-slate-500">
                                                Sin facturas SAT para el período seleccionado.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot class="bg-slate-50 font-semibold">
                                    <tr>
                                        <td colspan="4" class="px-4 py-3 text-sm text-slate-900">Total facturado</td>
                                        <td class="px-4 py-3 text-right text-sm text-slate-900">$ {{ number_format($totalSat, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                @if($tab === 'gerencial')
                <div class="space-y-6">
                    <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
                        <div class="bg-slate-100 px-6 py-4">
                            <h3 class="text-base font-medium text-slate-900">SAT base</h3>
                            <p class="mt-0.5 text-sm text-slate-500">Total facturado registrado en SAT</p>
                        </div>
                        <div class="px-6 py-4">
                            <p class="text-3xl font-semibold text-slate-900">$ {{ number_format($totalSat, 2) }}</p>
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
                        <div class="bg-gpt-100 px-6 py-4">
                            <h3 class="text-base font-medium text-slate-900">Devengado</h3>
                            <p class="mt-0.5 text-sm text-slate-600">Proyectos con OC firmada y avance del mes</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">DN</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Cliente</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">OC firmada</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Avance mes %</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Método</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Monto devengado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse($proyectosDevengado as $proyecto)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $proyecto['dn'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ $proyecto['cliente'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-center">
                                                @if($proyecto['oc_firmada'] ?? false)
                                                    <svg class="mx-auto h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                @else
                                                    <span class="text-xs text-slate-300">—</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-slate-700">{{ $proyecto['avance_mes'] ?? 0 }}%</td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ $proyecto['metodo'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-right text-sm font-medium text-slate-900">$ {{ number_format($proyecto['monto_devengado'] ?? 0, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500">
                                                Sin proyectos devengados para el período.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
                        <div class="bg-blue-50 px-6 py-4">
                            <h3 class="text-base font-medium text-slate-900">Pipeline ponderado</h3>
                            <p class="mt-0.5 text-sm text-slate-600">Cotizaciones presentadas con probabilidad de cierre</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-white">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">CP</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Cliente</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Sublinea</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Probabilidad %</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Monto ofertado</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Monto ponderado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse($pipelinePonderado as $item)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-3 text-sm font-medium text-slate-900">{{ $item['cp'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ $item['cliente'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ $item['sublinea'] ?? '—' }}</td>
                                            <td class="px-4 py-3 text-right text-sm text-slate-700">{{ $item['probabilidad'] ?? 0 }}%</td>
                                            <td class="px-4 py-3 text-right text-sm text-slate-700">$ {{ number_format($item['monto_ofertado'] ?? 0, 2) }}</td>
                                            <td class="px-4 py-3 text-right text-sm font-medium text-slate-900">$ {{ number_format($item['monto_ponderado'] ?? 0, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-12 text-center text-sm text-slate-500">
                                                Sin pipeline ponderado para el período.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <span class="text-base font-medium text-slate-900">Total gerencial</span>
                            <span class="text-2xl font-bold text-slate-900">$ {{ number_format($totalGerencial, 2) }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">SAT + Devengado + Pipeline ponderado</p>
                    </div>
                </div>
                @endif

                @if($tab === 'comparativa')
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 bg-white p-6">
                            <p class="text-sm font-medium text-slate-500">Cierre SAT</p>
                            <p class="mt-2 text-3xl font-semibold text-slate-900">$ {{ number_format($totalSat, 2) }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-white p-6">
                            <p class="text-sm font-medium text-slate-500">Cierre Gerencial</p>
                            <p class="mt-2 text-3xl font-semibold text-slate-900">$ {{ number_format($totalGerencial, 2) }}</p>
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white p-6">
                        <p class="text-sm font-medium text-slate-500">Delta</p>
                        <div class="mt-2 flex items-baseline gap-3">
                            <span class="text-2xl font-semibold {{ $delta >= 0 ? 'text-emerald-600' : 'text-gpt-red-600' }}">
                                {{ $delta >= 0 ? '+' : '' }}$ {{ number_format($delta, 2) }}
                            </span>
                            <span class="text-lg {{ $delta >= 0 ? 'text-emerald-600' : 'text-gpt-red-600' }}">
                                ({{ $delta >= 0 ? '+' : '' }}{{ $deltaPct }}%)
                            </span>
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white p-6">
                        <h3 class="text-sm font-medium text-slate-900 mb-4">Composición del cierre gerencial</h3>
                        <div class="flex h-48 items-end gap-3">
                            @php
                                $maxVal = max($totalGerencial, 1);
                            @endphp
                            <div class="flex flex-1 flex-col items-center gap-2">
                                <span class="text-xs font-medium text-slate-500">$ {{ number_format($totalSat / 1000, 0) }}k</span>
                                <div class="w-full rounded-t bg-slate-400" style="height: {{ min($totalSat / $maxVal * 140, 140) }}px"></div>
                                <span class="text-xs text-slate-500">SAT</span>
                            </div>
                            <div class="flex flex-1 flex-col items-center gap-2">
                                <span class="text-xs font-medium text-slate-500">$ {{ number_format($totalDevengado / 1000, 0) }}k</span>
                                <div class="w-full rounded-t bg-gpt-400" style="height: {{ min($totalDevengado / $maxVal * 140, 140) }}px"></div>
                                <span class="text-xs text-slate-500">Devengado</span>
                            </div>
                            <div class="flex flex-1 flex-col items-center gap-2">
                                <span class="text-xs font-medium text-slate-500">$ {{ number_format($totalPipeline / 1000, 0) }}k</span>
                                <div class="w-full rounded-t bg-blue-400" style="height: {{ min($totalPipeline / $maxVal * 140, 140) }}px"></div>
                                <span class="text-xs text-slate-500">Pipeline</span>
                            </div>
                            <div class="flex flex-1 flex-col items-center gap-2">
                                <span class="text-xs font-bold text-slate-900">$ {{ number_format($totalGerencial / 1000, 0) }}k</span>
                                <div class="w-full rounded-t bg-slate-700" style="height: {{ min($totalGerencial / $maxVal * 140, 140) }}px"></div>
                                <span class="text-xs font-medium text-slate-700">Total</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white p-6">
                        <h3 class="text-sm font-medium text-slate-900 mb-4">12 meses — Evolución comparativa</h3>
                        <div class="flex h-48 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50">
                            <div class="text-center">
                                <p class="text-sm text-slate-400">Gráfico de líneas — 12 meses</p>
                                <p class="mt-1 text-xs text-slate-400">Series: SAT · Gerencial</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="w-full lg:w-72 lg:flex-shrink-0">
                <div class="lg:sticky lg:top-6 space-y-4">
                    <div class="rounded-lg border border-slate-200 bg-white p-5">
                        <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Estado</h4>
                        <div class="mt-3">
                            @php
                                $statusColors = [
                                    'borrador' => 'bg-slate-100 text-slate-700',
                                    'generado' => 'bg-amber-100 text-amber-800',
                                    'aprobado' => 'bg-emerald-100 text-emerald-800',
                                ];
                                $statusLabels = [
                                    'borrador' => 'Borrador',
                                    'generado' => 'Generado',
                                    'aprobado' => 'Cerrado',
                                ];
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$workflowStatus] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $statusLabels[$workflowStatus] ?? 'Borrador' }}
                            </span>
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white p-5 space-y-3">
                        <div>
                            <p class="text-xs text-slate-500">Generado por</p>
                            <p class="text-sm font-medium text-slate-900">{{ $generadoPor }}</p>
                            <p class="text-xs text-slate-400">{{ $fechaGeneracion }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Revisado por CFO</p>
                            <p class="text-sm font-medium text-slate-900">{{ $revisadoCfo }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Aprobado por DG</p>
                            <p class="text-sm font-medium text-slate-900">{{ $aprobadoDg }}</p>
                        </div>
                    </div>

                    @can('aprobar cierre')
                    @if($workflowStatus === 'generado')
                    <button type="button" wire:click="aprobarCierre" wire:confirm="¿Aprobar y bloquear este cierre? Esta acción no se puede deshacer." class="w-full rounded-lg bg-gpt-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                        <svg class="mr-1.5 inline-block h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Aprobar y bloquear
                    </button>
                    @elseif($workflowStatus === 'aprobado')
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-center text-sm font-medium text-emerald-700">
                        Cierre aprobado y bloqueado
                    </div>
                    @endif
                    @endcan

                    <div class="rounded-lg border border-slate-200 bg-white p-5">
                        <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Historial</h4>
                        <div class="mt-3 space-y-3">
                            @forelse($auditEntries as $entry)
                                <div class="flex gap-3">
                                    <div class="relative flex flex-col items-center">
                                        <div class="h-2.5 w-2.5 rounded-full border-2 border-gpt-600 bg-white"></div>
                                        <div class="w-px flex-1 bg-slate-200 {{ $loop->last ? 'hidden' : '' }}"></div>
                                    </div>
                                    <div class="pb-3">
                                        <p class="text-sm font-medium text-slate-900">{{ $entry['accion'] ?? '—' }}</p>
                                        <p class="text-xs text-slate-500">{{ $entry['fecha'] ?? '—' }}</p>
                                        @if(!empty($entry['usuario']))
                                            <p class="text-xs text-slate-400">{{ $entry['usuario'] }}</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-slate-400">Sin registros de auditoría.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                        <div class="flex items-start gap-2">
                            <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <p class="text-xs text-amber-700">Solo CFO, Dirección General, Socios y Comité de Socios pueden acceder a esta sección.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>