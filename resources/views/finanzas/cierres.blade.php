<x-layouts.app>
    <div class="space-y-6" x-data="cierresApp()">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-medium text-slate-900">Cierres Mensuales</h2>
                <p class="mt-1 text-sm text-slate-500">Comparativa SAT vs Gerencial · Cierre mensual D1</p>
            </div>
        </div>

        {{-- Controls --}}
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Año</label>
                    <select x-model="filtros.anio" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                        @foreach(($anios_disponibles ?? [date('Y'), date('Y')-1, date('Y')-2]) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Mes</label>
                    <select x-model="filtros.mes" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                        @foreach(['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'] as $i => $nombre)
                            <option value="{{ $i + 1 }}" @selected(($mes ?? date('n')) == $i + 1)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="button" @click="generarCierre()" :disabled="generando" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Generar cierre del mes
                    <span class="rounded-full bg-white/20 px-1.5 py-0.5 text-xs">~30 segundos</span>
                </button>
                <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Exportar PDF
                </button>
            </div>

            {{-- Generating progress --}}
            <div x-show="generando" x-cloak class="mt-4 space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-600" x-text="progresoTexto"></span>
                    <span class="text-slate-400" x-text="progresoEtapa + '/3'"></span>
                </div>
                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-gpt-600 transition-all duration-700" :style="'width: ' + progresoPorcentaje + '%'"></div>
                </div>
            </div>
        </div>

        {{-- Main content + Sidebar --}}
        <div class="flex flex-col gap-6 lg:flex-row">
            {{-- Left: Tabs area --}}
            <div class="flex-1 min-w-0 space-y-6">
                {{-- Tabs --}}
                <div class="flex border-b border-slate-200">
                    <button @click="tab = 'sat'" :class="tab === 'sat' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="border-b-2 px-4 py-2.5 text-sm font-medium transition-colors">Cierre SAT</button>
                    <button @click="tab = 'gerencial'" :class="tab === 'gerencial' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="border-b-2 px-4 py-2.5 text-sm font-medium transition-colors">Cierre Gerencial</button>
                    <button @click="tab = 'comparativa'" :class="tab === 'comparativa' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="border-b-2 px-4 py-2.5 text-sm font-medium transition-colors">Comparativa</button>
                </div>

                {{-- TAB 1: Cierre SAT --}}
                <div x-show="tab === 'sat'" class="space-y-6">
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
                                    @forelse($facturas_sat ?? [] as $factura)
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
                                        <td class="px-4 py-3 text-right text-sm text-slate-900">$ {{ number_format($total_sat ?? 0, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="flex items-center gap-2" x-data="{ detalleVisible: false }">
                        <button @click="detalleVisible = !detalleVisible" class="inline-flex items-center gap-1.5 text-sm font-medium text-gpt-600 hover:text-gpt-700 transition-colors">
                            <svg class="h-4 w-4 transition-transform" :class="detalleVisible ? 'rotate-90' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            <span x-text="detalleVisible ? 'Ocultar detalle de facturas' : 'Ver detalle de facturas'"></span>
                        </button>
                    </div>
                </div>

                {{-- TAB 2: Cierre Gerencial --}}
                <div x-show="tab === 'gerencial'" class="space-y-6">
                    {{-- Section 1: SAT base --}}
                    <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
                        <div class="bg-slate-100 px-6 py-4">
                            <h3 class="text-base font-medium text-slate-900">SAT base</h3>
                            <p class="mt-0.5 text-sm text-slate-500">Total facturado registrado en SAT</p>
                        </div>
                        <div class="px-6 py-4">
                            <p class="text-3xl font-semibold text-slate-900">$ {{ number_format($total_sat ?? 0, 2) }}</p>
                        </div>
                    </div>

                    {{-- Section 2: Devengado --}}
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
                                    @forelse($proyectos_devengado ?? [] as $proyecto)
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

                    {{-- Section 3: Pipeline ponderado --}}
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
                                    @forelse($pipeline_ponderado ?? [] as $item)
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

                    {{-- Footer total --}}
                    @php
                        $total_devengado = collect($proyectos_devengado ?? [])->sum('monto_devengado');
                        $total_pipeline = collect($pipeline_ponderado ?? [])->sum('monto_ponderado');
                        $total_gerencial = ($total_sat ?? 0) + $total_devengado + $total_pipeline;
                    @endphp
                    <div class="rounded-lg border border-slate-200 bg-slate-50 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <span class="text-base font-medium text-slate-900">Total gerencial</span>
                            <span class="text-2xl font-bold text-slate-900">$ {{ number_format($total_gerencial, 2) }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">SAT + Devengado + Pipeline ponderado</p>
                    </div>
                </div>

                {{-- TAB 3: Comparativa --}}
                <div x-show="tab === 'comparativa'" class="space-y-6">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        @php
                            $total_devengado = collect($proyectos_devengado ?? [])->sum('monto_devengado');
                            $total_pipeline = collect($pipeline_ponderado ?? [])->sum('monto_ponderado');
                            $total_gerencial = ($total_sat ?? 0) + $total_devengado + $total_pipeline;
                            $delta = $total_gerencial - ($total_sat ?? 0);
                            $delta_pct = ($total_sat ?? 0) > 0 ? round(($delta / ($total_sat ?? 1)) * 100, 1) : 0;
                        @endphp
                        <div class="rounded-lg border border-slate-200 bg-white p-6">
                            <p class="text-sm font-medium text-slate-500">Cierre SAT</p>
                            <p class="mt-2 text-3xl font-semibold text-slate-900">$ {{ number_format($total_sat ?? 0, 2) }}</p>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-white p-6">
                            <p class="text-sm font-medium text-slate-500">Cierre Gerencial</p>
                            <p class="mt-2 text-3xl font-semibold text-slate-900">$ {{ number_format($total_gerencial, 2) }}</p>
                        </div>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white p-6">
                        <p class="text-sm font-medium text-slate-500">Delta</p>
                        <div class="mt-2 flex items-baseline gap-3">
                            <span class="text-2xl font-semibold {{ $delta >= 0 ? 'text-emerald-600' : 'text-gpt-red-600' }}">
                                {{ $delta >= 0 ? '+' : '' }}$ {{ number_format($delta, 2) }}
                            </span>
                            <span class="text-lg {{ $delta >= 0 ? 'text-emerald-600' : 'text-gpt-red-600' }}">
                                ({{ $delta >= 0 ? '+' : '' }}{{ $delta_pct }}%)
                            </span>
                        </div>
                    </div>

                    {{-- Waterfall chart placeholder --}}
                    <div class="rounded-lg border border-slate-200 bg-white p-6">
                        <h3 class="text-sm font-medium text-slate-900 mb-4">Composición del cierre gerencial</h3>
                        <div class="flex h-48 items-end gap-3">
                            <div class="flex flex-1 flex-col items-center gap-2">
                                <span class="text-xs font-medium text-slate-500">$ {{ number_format(($total_sat ?? 0) / 1000, 0) }}k</span>
                                <div class="w-full rounded-t bg-slate-400" style="height: {{ min(($total_sat ?? 0) / max($total_gerencial, 1) * 140, 140) }}px"></div>
                                <span class="text-xs text-slate-500">SAT</span>
                            </div>
                            <div class="flex flex-1 flex-col items-center gap-2">
                                <span class="text-xs font-medium text-slate-500">$ {{ number_format($total_devengado / 1000, 0) }}k</span>
                                <div class="w-full rounded-t bg-gpt-400" style="height: {{ min($total_devengado / max($total_gerencial, 1) * 140, 140) }}px"></div>
                                <span class="text-xs text-slate-500">Devengado</span>
                            </div>
                            <div class="flex flex-1 flex-col items-center gap-2">
                                <span class="text-xs font-medium text-slate-500">$ {{ number_format($total_pipeline / 1000, 0) }}k</span>
                                <div class="w-full rounded-t bg-blue-400" style="height: {{ min($total_pipeline / max($total_gerencial, 1) * 140, 140) }}px"></div>
                                <span class="text-xs text-slate-500">Pipeline</span>
                            </div>
                            <div class="flex flex-1 flex-col items-center gap-2">
                                <span class="text-xs font-bold text-slate-900">$ {{ number_format($total_gerencial / 1000, 0) }}k</span>
                                <div class="w-full rounded-t bg-slate-700" style="height: {{ min($total_gerencial / max($total_gerencial, 1) * 140, 140) }}px"></div>
                                <span class="text-xs font-medium text-slate-700">Total</span>
                            </div>
                        </div>
                    </div>

                    {{-- 12-month evolution chart placeholder --}}
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
            </div>

            {{-- Right Sidebar --}}
            <div class="w-full lg:w-72 lg:flex-shrink-0">
                <div class="lg:sticky lg:top-6 space-y-4">
                    {{-- Workflow status --}}
                    <div class="rounded-lg border border-slate-200 bg-white p-5">
                        <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Estado</h4>
                        <div class="mt-3">
                            @php
                                $status = $workflow_status ?? 'borrador';
                                $statusColors = [
                                    'borrador' => 'bg-slate-100 text-slate-700',
                                    'en_revision' => 'bg-amber-100 text-amber-800',
                                    'cerrado' => 'bg-emerald-100 text-emerald-800',
                                ];
                                $statusLabels = [
                                    'borrador' => 'Borrador',
                                    'en_revision' => 'En revisión',
                                    'cerrado' => 'Cerrado',
                                ];
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$status] }}">
                                {{ $statusLabels[$status] }}
                            </span>
                        </div>
                    </div>

                    {{-- Generated by --}}
                    <div class="rounded-lg border border-slate-200 bg-white p-5 space-y-3">
                        <div>
                            <p class="text-xs text-slate-500">Generado por</p>
                            <p class="text-sm font-medium text-slate-900">{{ $generado_por ?? '—' }}</p>
                            <p class="text-xs text-slate-400">{{ $fecha_generacion ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Revisado por CFO</p>
                            <p class="text-sm font-medium text-slate-900">{{ $revisado_cfo ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Aprobado por DG</p>
                            <p class="text-sm font-medium text-slate-900">{{ $aprobado_dg ?? '—' }}</p>
                        </div>
                    </div>

                    {{-- Approve button --}}
                    <button type="button" class="w-full rounded-lg bg-gpt-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                        <svg class="mr-1.5 inline-block h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Aprobar y bloquear
                    </button>

                    {{-- Audit timeline --}}
                    <div class="rounded-lg border border-slate-200 bg-white p-5">
                        <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Historial</h4>
                        <div class="mt-3 space-y-3">
                            @forelse($audit_entries ?? [] as $entry)
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

                    {{-- Access restriction --}}
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
</x-layouts.app>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('cierresApp', () => ({
        filtros: {
            anio: '{{ $anio ?? date('Y') }}',
            mes: '{{ $mes ?? date('n') }}',
        },
        tab: 'sat',
        generando: false,
        progresoEtapa: 0,
        progresoTexto: '',
        progresoPorcentaje: 0,

        async generarCierre() {
            this.generando = true;
            this.progresoPorcentaje = 0;
            this.progresoEtapa = 0;

            const etapas = [
                'Calculando SAT base...',
                'Calculando Devengado...',
                'Calculando Pipeline ponderado...',
                '¡Listo!',
            ];

            for (let i = 0; i < etapas.length; i++) {
                this.progresoTexto = etapas[i];
                this.progresoEtapa = Math.min(i + 1, 3);
                this.progresoPorcentaje = ((i + 1) / 3) * 100;
                await new Promise(r => setTimeout(r, 1200));
            }

            this.generando = false;
            // Reload page or refresh data via Livewire
            window.location.reload();
        }
    }));
});
</script>