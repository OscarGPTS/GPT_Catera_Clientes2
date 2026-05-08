<div>
    @section('title', 'Reporte de Asignación')

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-medium text-slate-900">Reporte de Asignación</h2>
                    <span class="rounded-full bg-gpt-100 px-2.5 py-0.5 text-xs font-medium text-gpt-800">Q{{ $trimestre }} {{ $anio }}</span>
                </div>
                <p class="mt-1 text-sm text-slate-500">FO-GPT-PYT-01-B · Distribución de carga por persona y mes</p>
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
                    <label for="anio" class="block text-sm font-medium text-slate-700">Año</label>
                    <select id="anio" wire:model.live="anio" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                        @foreach($anios as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="trimestre" class="block text-sm font-medium text-slate-700">Trimestre</label>
                    <select id="trimestre" wire:model.live="trimestre" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                        <option value="1">Q1</option>
                        <option value="2">Q2</option>
                        <option value="3">Q3</option>
                        <option value="4">Q4</option>
                    </select>
                </div>
                <div>
                    <label for="gerencia" class="block text-sm font-medium text-slate-700">Gerencia Regional</label>
                    <select id="gerencia" wire:model.live="gerencia" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                        <option value="">Todas</option>
                        <option value="GRC">GRC</option>
                        <option value="GRS">GRS</option>
                        <option value="GRN">GRN</option>
                        <option value="DG">DG</option>
                        <option value="GPT-IM">GPT-IM</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Exportar Excel
                    </button>
                    @can('admin')
                    <button type="button" wire:click="generarSnapshot" wire:confirm="¿Generar snapshot de asignación para el período actual?" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Generar snapshot
                    </button>
                    @endcan
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg border border-slate-200 bg-white p-4">
                <p class="text-sm font-medium text-slate-600">Equipo total</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $equipoTotal }}</p>
                <p class="mt-1 text-xs text-slate-500">personas</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4">
                <p class="text-sm font-medium text-slate-600">Proyectos activos</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $proyectosActivos }}</p>
                <p class="mt-1 text-xs text-slate-500">en ejecución</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white p-4">
                <p class="text-sm font-medium text-slate-600">Carga promedio</p>
                <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $cargaPromedio }}</p>
                <p class="mt-1 text-xs text-slate-500">proyectos / persona</p>
            </div>
            <div class="rounded-lg border border-slate-200 {{ $sobrecarga > 0 ? 'border-gpt-red-200 bg-gpt-red-50' : 'border-slate-200 bg-white' }} p-4">
                <p class="text-sm font-medium {{ $sobrecarga > 0 ? 'text-gpt-red-700' : 'text-slate-600' }}">Sobrecarga</p>
                <p class="mt-2 text-3xl font-semibold {{ $sobrecarga > 0 ? 'text-gpt-red-700' : 'text-slate-900' }}">{{ $sobrecarga }}</p>
                <p class="mt-1 text-xs {{ $sobrecarga > 0 ? 'text-gpt-red-600' : 'text-slate-500' }}">{{ $sobrecarga > 0 ? 'personas con 10+' : 'sin sobrecarga' }}</p>
            </div>
        </div>

        @if(!$snapshotGenerado)
        <div class="rounded-lg border border-slate-200 bg-white p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
            <h3 class="mt-4 text-lg font-medium text-slate-900">Snapshot del mes aún no generado</h3>
            <p class="mt-1 text-sm text-slate-500">Genera el snapshot para ver la asignación del equipo.</p>
            @can('admin')
            <button type="button" wire:click="generarSnapshot" wire:confirm="¿Generar snapshot de asignación para Q{{ $trimestre }} {{ $anio }}?" class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Generar snapshot
            </button>
            @endcan
        </div>
        @else

        <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="sticky left-0 z-20 bg-slate-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 min-w-[220px]">Persona</th>
                            @foreach(['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'] as $mes)
                                <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500 w-[64px]">{{ $mes }}</th>
                            @endforeach
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Promedio</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($asignaciones as $idx => $persona)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="sticky left-0 z-10 bg-white px-4 py-2.5">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gpt-100 text-xs font-semibold text-gpt-700">
                                            {{ strtoupper(substr($persona['nombre'] ?? '', 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-slate-900 truncate">{{ $persona['nombre'] ?? '—' }}</p>
                                            <p class="text-xs text-slate-500 truncate">{{ $persona['puesto'] ?? '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                @foreach($persona['meses'] ?? array_fill(0, 12, null) as $mi => $carga)
                                    <td class="px-2 py-2.5 text-center">
                                        @if($carga !== null)
                                            @php
                                                $colorClass = match(true) {
                                                    $carga >= 10 => 'bg-gpt-red-200 text-gpt-red-900',
                                                    $carga >= 8 => 'bg-gpt-200 text-gpt-900',
                                                    $carga >= 5 => 'bg-amber-100 text-amber-900',
                                                    default => 'bg-green-100 text-green-900',
                                                };
                                            @endphp
                                            <button type="button" wire:click="openDrawer({{ $idx }})" class="w-full rounded p-2 text-center text-sm font-medium {{ $colorClass }} hover:ring-2 hover:ring-slate-400 transition-all cursor-pointer">
                                                {{ $carga }}
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-300">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-4 py-2.5 text-center text-sm font-medium text-slate-700">
                                    {{ $persona['promedio'] ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="px-4 py-12 text-center text-sm text-slate-500">
                                    Sin datos de asignación para los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">
                <div class="flex flex-wrap items-center gap-4 text-xs">
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block h-4 w-4 rounded bg-green-100"></span>
                        <span class="text-slate-600">0-4 Carga baja</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block h-4 w-4 rounded bg-amber-100"></span>
                        <span class="text-slate-600">5-7 Carga media</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block h-4 w-4 rounded bg-gpt-200"></span>
                        <span class="text-slate-600">8-9 Carga alta</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="inline-block h-4 w-4 rounded bg-gpt-red-200"></span>
                        <span class="text-slate-600">10+ Sobrecarga</span>
                    </div>
                </div>
            </div>

            {{-- Detail Drawer --}}
            @if($drawerOpen && $drawerPersona)
            <div class="fixed inset-0 z-50 overflow-hidden" role="dialog" aria-modal="true">
                <div class="absolute inset-0 bg-slate-900/30" wire:click="closeDrawer"></div>
                <div class="absolute inset-y-0 right-0 flex max-w-full">
                    <div class="w-screen max-w-lg bg-white shadow-xl">
                        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                            <h3 class="text-lg font-medium text-slate-900">Detalle de asignación</h3>
                            <button wire:click="closeDrawer" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <div class="flex-1 overflow-y-auto p-6" x-data="{ tab: 'actual' }">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gpt-100 text-xl font-semibold text-gpt-700">
                                    {{ strtoupper(substr($drawerPersona['nombre'] ?? '', 0, 2)) }}
                                </div>
                                <div>
                                    <p class="text-xl font-medium text-slate-900">{{ $drawerPersona['nombre'] ?? '—' }}</p>
                                    <p class="text-sm text-slate-500">{{ $drawerPersona['puesto'] ?? '—' }}</p>
                                    <p class="text-xs text-slate-400">Desde {{ $drawerPersona['antiguedad'] ?? '—' }}</p>
                                </div>
                            </div>

                            <div class="flex border-b border-slate-200 mb-4">
                                <button @click="tab = 'actual'" :class="tab === 'actual' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="border-b-2 px-4 py-2 text-sm font-medium transition-colors">Asignación actual</button>
                                <button @click="tab = 'historico'" :class="tab === 'historico' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="border-b-2 px-4 py-2 text-sm font-medium transition-colors">Histórico</button>
                                <button @click="tab = 'rh'" :class="tab === 'rh' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700'" class="border-b-2 px-4 py-2 text-sm font-medium transition-colors">Datos RH</button>
                            </div>

                            <div x-show="tab === 'actual'">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                        <p class="text-xs text-slate-500">CP asignados</p>
                                        <p class="mt-1 text-lg font-semibold text-slate-900">{{ $drawerPersona['cp_asignados'] ?? 0 }}</p>
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                        <p class="text-xs text-slate-500">CP ejecutados</p>
                                        <p class="mt-1 text-lg font-semibold text-slate-900">{{ $drawerPersona['cp_ejecutados'] ?? 0 }}</p>
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                        <p class="text-xs text-slate-500">CP remanentes</p>
                                        <p class="mt-1 text-lg font-semibold text-slate-900">{{ $drawerPersona['cp_remanentes'] ?? 0 }}</p>
                                    </div>
                                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                        <p class="text-xs text-slate-500">Residual</p>
                                        <p class="mt-1 text-lg font-semibold text-slate-900">{{ $drawerPersona['residual'] ?? 0 }}</p>
                                    </div>
                                </div>
                            </div>

                            <div x-show="tab === 'historico'">
                                <div class="flex h-48 items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50">
                                    <p class="text-sm text-slate-400">Gráfico de línea — 12 meses</p>
                                </div>
                            </div>

                            <div x-show="tab === 'rh'">
                                <dl class="space-y-3">
                                    <div class="flex justify-between border-b border-slate-100 pb-2">
                                        <dt class="text-sm text-slate-500">Puesto RH</dt>
                                        <dd class="text-sm font-medium text-slate-900">{{ $drawerPersona['puesto_rh'] ?? '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between border-b border-slate-100 pb-2">
                                        <dt class="text-sm text-slate-500">Ingreso</dt>
                                        <dd class="text-sm font-medium text-slate-900">{{ $drawerPersona['fecha_ingreso'] ?? '—' }}</dd>
                                    </div>
                                    <div class="flex justify-between border-b border-slate-100 pb-2">
                                        <dt class="text-sm text-slate-500">Formación</dt>
                                        <dd class="text-sm font-medium text-slate-900">{{ $drawerPersona['formacion'] ?? '—' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
            <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                <h3 class="text-base font-medium text-slate-900">Detalle por persona</h3>
                <p class="mt-0.5 text-sm text-slate-500">CP totales, DN totales, servicio, suministro y carga actual</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-white">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Persona</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Posición</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Gerencia</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">CP totales</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">DN totales</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Servicio</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Suministro</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Cerrados Q</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Carga actual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($asignaciones as $persona)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gpt-100 text-xs font-semibold text-gpt-700">
                                            {{ strtoupper(substr($persona['nombre'] ?? '', 0, 2)) }}
                                        </div>
                                        <span class="text-sm font-medium text-slate-900">{{ $persona['nombre'] ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $persona['puesto'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600">{{ $persona['gerencia'] ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-slate-900">{{ $persona['cp_asignados'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-right text-sm text-slate-700">{{ $persona['dn_totales'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-right text-sm text-slate-700">{{ $persona['servicio'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-right text-sm text-slate-700">{{ $persona['suministro'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-right text-sm text-slate-700">{{ $persona['cerrados_q'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-slate-900">{{ $persona['carga_actual'] ?? 0 }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-sm text-slate-500">
                                    Sin datos de detalle disponibles.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>