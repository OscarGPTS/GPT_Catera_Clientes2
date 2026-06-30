<div>
    {{-- Breadcrumb & Header --}}
    <div class="mb-2">
        <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
            <a href="{{ route('oportunidades.index') }}" class="hover:text-slate-700 transition-colors">Oportunidades</a>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-slate-900">{{ $proyecto->cp_numero ?? 'CP-?' }}</span>
        </nav>
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="text-xl sm:text-2xl font-medium text-slate-900">{{ $proyecto->tech_reference ?? $proyecto->cp_numero ?? 'Oportunidad' }}</h2>
            <x-badge :status="$proyecto->estado" />
        </div>
        <p class="mt-1 text-sm text-slate-500">{{ $proyecto->cliente->razon_social ?? '—' }}{{ $proyecto->usuario_final ? ' — ' . $proyecto->usuario_final : '' }}</p>
    </div>

    {{-- Header actions --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <span class="inline-flex items-center rounded-full bg-gpt-100 px-3 py-1 text-sm font-medium text-gpt-800">
            {{ $proyecto->sublinea->nombre ?? 'Sin sublínea' }}
        </span>
        @if($proyecto->sector)
            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-600">{{ $proyecto->sector }}</span>
        @endif
        <span class="text-sm text-slate-500">{{ $proyecto->año }}</span>

        <div class="ml-auto flex items-center gap-2">
            @can('aprobar cp')
                @if($proyecto->estado === 'en_revision')
                    <button type="button" wire:click="$set('showAprobarModal', true)"
                            class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-green-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Aprobar CP
                    </button>
                    <button type="button" wire:click="$set('showRechazarModal', true)"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gpt-red-300 bg-white px-4 py-2 text-sm font-medium text-gpt-red-600 shadow-sm hover:bg-gpt-red-50 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        Rechazar
                    </button>
                @endif
            @endcan
            @unlessrole('invitado')
            <button type="button" wire:click="$set('showEstadoModal', true)"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                Cambiar estado
            </button>
            <button type="button" wire:click="abrirPonderacionModal"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                Ponderación
            </button>
            @endunlessrole
            <a href="{{ route('proyectos.cotizacion', $proyecto) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Cotización
            </a>
            <a href="{{ route('proyectos.minuta', $proyecto) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                Minuta
            </a>
        </div>
    </div>

    {{-- Success flash --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
             class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div class="mb-6 border-b border-slate-200">
        <nav class="flex gap-6 -mb-px overflow-x-auto" aria-label="Tabs">
            @foreach(['info' => 'Información', 'equipo' => 'Equipo', 'cotizaciones' => 'Cotizaciones', 'solicitudes' => 'Solicitudes', 'eventos' => 'Historial'] as $key => $label)
                <button wire:click="$set('activeTab', '{{ $key }}')"
                        class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors {{ $activeTab === $key ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:border-slate-300 hover:text-slate-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </nav>
    </div>

    {{-- Tab: Información --}}
    @if($activeTab === 'info')
        <div class="space-y-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-lg border border-slate-200 bg-white p-6">
                        <h3 class="text-base font-medium text-slate-900 mb-4">Datos generales</h3>
                        <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-slate-500">CP</dt>
                                <dd class="mt-1 text-sm text-slate-900 font-mono">{{ $proyecto->cp_numero ?? 'Sin asignar' }}</dd>
                            </div>
                            @if($proyecto->dn_numero)
                                <div>
                                    <dt class="text-sm font-medium text-slate-500">DN</dt>
                                    <dd class="mt-1 text-sm text-slate-900 font-mono">{{ $proyecto->dn_numero }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Tech Reference</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $proyecto->tech_reference ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Cliente</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $proyecto->cliente->razon_social ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Usuario final</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $proyecto->usuario_final ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Sublínea</dt>
                                <dd class="mt-1">
                                    @if($proyecto->sublinea)
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-gpt-100 text-gpt-800">{{ $proyecto->sublinea->nombre }}</span>
                                    @else
                                        <span class="text-sm text-slate-900">—</span>
                                    @endif
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Sector</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $proyecto->sector ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Método distribución plurianual</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $proyecto->metodo_distribucion_plurianual === 'dias_naturales' ? 'Días naturales' : 'Hitos' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Fecha inicio planeada</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $proyecto->fecha_inicio_planeada?->format('d/m/Y') ?? '—' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-slate-500">Fecha fin planeada</dt>
                                <dd class="mt-1 text-sm text-slate-900">{{ $proyecto->fecha_fin_planeada?->format('d/m/Y') ?? '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                    @if($proyecto->notas)
                        <div class="rounded-lg border border-slate-200 bg-white p-6">
                            <h3 class="text-base font-medium text-slate-900 mb-2">Notas</h3>
                            <p class="text-sm text-slate-700 whitespace-pre-line">{{ $proyecto->notas }}</p>
                        </div>
                    @endif
                    @if($proyecto->cliente && $proyecto->cliente->contactos->count() > 0)
                        <div class="rounded-lg border border-slate-200 bg-white p-6">
                            <h3 class="text-base font-medium text-slate-900 mb-4">Contactos del cliente</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nombre</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Puesto</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Email</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Teléfono</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 bg-white">
                                        @foreach($proyecto->cliente->contactos as $contacto)
                                            <tr class="hover:bg-slate-50 transition-colors">
                                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900">{{ $contacto->nombre ?? '—' }}</td>
                                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $contacto->puesto ?? '—' }}</td>
                                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gpt-600">{{ $contacto->email ?? '—' }}</td>
                                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $contacto->telefono ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="space-y-6">
                    <div class="rounded-lg border border-slate-200 bg-white p-5">
                        <h3 class="text-sm font-semibold text-slate-900 mb-3">Estado actual</h3>
                        <div class="flex items-center gap-3">
                            <x-badge :status="$proyecto->estado" />
                            <span class="text-sm text-slate-500">{{ $proyecto->updated_at?->diffForHumans() ?? '' }}</span>
                        </div>
                        @if($proyecto->fecha_inicio_planeada && $proyecto->fecha_fin_planeada)
                            <div class="mt-4">
                                <p class="text-sm font-medium text-slate-700">Duración planeada</p>
                                <p class="text-sm text-slate-500">{{ $proyecto->fecha_inicio_planeada->format('d/m/Y') }} — {{ $proyecto->fecha_fin_planeada->format('d/m/Y') }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Ponderación + historial mensual de cambios --}}
                    <div class="rounded-lg border border-slate-200 bg-white p-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-sm font-semibold text-slate-900">Ponderación</h3>
                            @unlessrole('invitado')
                                <button type="button" wire:click="abrirPonderacionModal" class="text-xs font-medium text-gpt-600 hover:text-gpt-700 transition-colors">Actualizar</button>
                            @endunlessrole
                        </div>

                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-semibold text-slate-900">{{ (int) $proyecto->ponderacion }}%</span>
                            <span class="text-sm text-slate-500">probabilidad actual</span>
                        </div>

                        @php
                            $mesesPanel = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                            $colorPanel = ['red' => '#ef4444', 'blue' => '#3b82f6', 'yellow' => '#eab308', 'green' => '#22c55e'];
                            $histPanel = $proyecto->historialPonderacion->sortByDesc('id');
                        @endphp

                        @if($histPanel->isNotEmpty())
                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-2">Historial de cambios</p>
                                <ul class="space-y-2 max-h-56 overflow-y-auto">
                                    @foreach($histPanel as $h)
                                        <li>
                                            <div class="flex items-center justify-between gap-2 text-xs">
                                                <span class="inline-flex items-center gap-2 text-slate-600">
                                                    <span class="inline-block h-2 w-2 rounded-full" style="background: {{ $colorPanel[$h->ponderacion->color ?? ''] ?? '#94a3b8' }}"></span>
                                                    {{ $mesesPanel[$h->mes - 1] ?? '?' }} {{ $h->anio }}
                                                </span>
                                                <span class="font-medium text-slate-800">{{ $h->ponderacion->concepto ?? '—' }} ({{ $h->ponderacion->porcentaje ?? '?' }}%)</span>
                                            </div>
                                            @if($h->notas)
                                                <p class="mt-0.5 pl-4 text-[11px] text-slate-400">{{ $h->notas }}</p>
                                            @endif
                                            <p class="pl-4 text-[10px] text-slate-300">{{ $h->user?->name ?? 'Sistema' }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <p class="mt-3 text-xs text-slate-400">Sin snapshots de ponderación registrados.</p>
                        @endif
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-white p-5">
                        <h3 class="text-sm font-semibold text-slate-900 mb-3">Equipo asignado</h3>
                        <dl class="space-y-3">
                            @foreach([
                                ['label' => 'Director DN', 'user' => $proyecto->directorDn],
                                ['label' => 'Gerente Proyectos', 'user' => $proyecto->gerenteProyectos],
                                ['label' => 'Gerente Operaciones', 'user' => $proyecto->gerenteOperaciones],
                                ['label' => 'Ing. Costos', 'user' => $proyecto->ingenieroCostos],
                                ['label' => 'Ing. Proyectos', 'user' => $proyecto->ingenieroProyectos],
                                ['label' => 'Trainee', 'user' => $proyecto->trainee],
                            ] as $role)
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm text-slate-500">{{ $role['label'] }}</dt>
                                    <dd class="text-sm font-medium text-slate-900">
                                        @if($role['user'])
                                            <span class="inline-flex items-center gap-1.5">
                                                <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-gpt-100 text-xs font-semibold text-gpt-700">
                                                    {{ collect(explode(' ', $role['user']->name))->map(fn($n) => mb_strtoupper(mb_substr($n, 0, 1)))->take(2)->implode('') }}
                                                </span>
                                                {{ $role['user']->name }}
                                            </span>
                                        @else
                                            <span class="text-slate-400">Sin asignar</span>
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                        @can('asignar cp')
                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <button type="button" wire:click="$set('showEquipoModal', true)" class="text-sm font-medium text-gpt-600 hover:text-gpt-700 transition-colors">Asignar equipo</button>
                            </div>
                        @endcan
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-white p-5">
                        <h3 class="text-sm font-semibold text-slate-900 mb-3">Acciones rápidas</h3>
                        <div class="space-y-2">
                            <a href="{{ route('proyectos.cotizacion', $proyecto) }}" class="flex items-center gap-2 text-sm text-slate-700 hover:text-gpt-600 transition-colors">
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Cotización
                            </a>
                            <a href="{{ route('proyectos.minuta', $proyecto) }}" class="flex items-center gap-2 text-sm text-slate-700 hover:text-gpt-600 transition-colors">
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                Minuta de entrega
                            </a>
                            <a href="{{ route('proyectos.libro', $proyecto->id) }}" class="flex items-center gap-2 text-sm text-slate-700 hover:text-gpt-600 transition-colors">
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                Libro de proyecto
                            </a>
                            <a href="{{ route('proyectos.bom-boe') }}?proyecto={{ $proyecto->id }}" class="flex items-center gap-2 text-sm text-slate-700 hover:text-gpt-600 transition-colors">
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0"/></svg>
                                BOM / BOE
                            </a>
                        </div>
                    </div>
                    @if($proyecto->created_at || $proyecto->updated_at)
                        <div class="rounded-lg border border-slate-200 bg-white p-5">
                            <h3 class="text-sm font-semibold text-slate-900 mb-3">Registro</h3>
                            <dl class="space-y-2">
                                <div class="flex justify-between text-sm">
                                    <dt class="text-slate-500">Creado</dt>
                                    <dd class="text-slate-900">{{ $proyecto->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <dt class="text-slate-500">Actualizado</dt>
                                    <dd class="text-slate-900">{{ $proyecto->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                                </div>
                            </dl>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Tab: Equipo --}}
    @if($activeTab === 'equipo')
        <div class="space-y-6">
            <div class="rounded-lg border border-slate-200 bg-white p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
                    <h3 class="text-base font-medium text-slate-900">Equipo del proyecto</h3>
                    @can('asignar cp')
                        <button type="button" wire:click="$set('showEquipoModal', true)" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Asignar equipo
                        </button>
                    @endcan
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach([
                        ['label' => 'Director DN', 'user' => $proyecto->directorDn],
                        ['label' => 'Gerente de Proyectos', 'user' => $proyecto->gerenteProyectos],
                        ['label' => 'Gerente de Operaciones', 'user' => $proyecto->gerenteOperaciones],
                        ['label' => 'Ingeniero de Costos', 'user' => $proyecto->ingenieroCostos],
                        ['label' => 'Ingeniero de Proyectos', 'user' => $proyecto->ingenieroProyectos],
                        ['label' => 'Trainee', 'user' => $proyecto->trainee],
                    ] as $role)
                        <div class="rounded-lg border {{ $role['user'] ? 'border-slate-200' : 'border-dashed border-slate-200' }} bg-slate-50 p-4">
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ $role['label'] }}</p>
                            @if($role['user'])
                                <div class="mt-3 flex items-center gap-3">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gpt-100 text-sm font-semibold text-gpt-700">
                                        {{ collect(explode(' ', $role['user']->name))->map(fn($n) => mb_strtoupper(mb_substr($n, 0, 1)))->take(2)->implode('') }}
                                    </span>
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">{{ $role['user']->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $role['user']->email }}</p>
                                    </div>
                                </div>
                            @else
                                <div class="mt-3 flex items-center gap-3">
                                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 text-sm font-medium text-slate-500">?</span>
                                    <p class="text-sm text-slate-400">Sin asignar</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- Tab: Cotizaciones --}}
    @if($activeTab === 'cotizaciones')
        <div class="space-y-6">
            <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-base font-medium text-slate-900">Cotizaciones</h3>
                        <p class="mt-0.5 text-sm text-slate-500">{{ $proyecto->cotizaciones->count() }} versión(es) registrada(s)</p>
                    </div>
                    <a href="{{ route('proyectos.cotizacion', $proyecto) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Nueva cotización
                    </a>
                </div>
                @if($proyecto->cotizaciones->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Versión</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Costo directo</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Precio venta</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Moneda</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fecha</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($proyecto->cotizaciones as $cot)
                                    @php
                                        $cotStatusColors = match($cot->status) {
                                            'borrador' => 'bg-slate-100 text-slate-700',
                                            'revision' => 'bg-amber-100 text-amber-800',
                                            'interno_aprobado' => 'bg-blue-100 text-blue-800',
                                            'presentado' => 'bg-gpt-100 text-gpt-800',
                                            'aprobado' => 'bg-green-100 text-green-800',
                                            'rechazado' => 'bg-gpt-red-100 text-gpt-red-800',
                                            default => 'bg-slate-100 text-slate-700',
                                        };
                                        $cotStatusLabel = match($cot->status) {
                                            'borrador' => 'Borrador',
                                            'revision' => 'Revisión',
                                            'interno_aprobado' => 'Int. aprobado',
                                            'presentado' => 'Presentado',
                                            'aprobado' => 'Aprobado',
                                            'rechazado' => 'Rechazado',
                                            default => $cot->status,
                                        };
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900">v{{ $cot->version }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-slate-900">$ {{ number_format($cot->costo_directo, 2, '.', ',') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-slate-900">$ {{ number_format($cot->precio_venta_final, 2, '.', ',') }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $cot->moneda }}</td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $cotStatusColors }}">{{ $cotStatusLabel }}</span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500">{{ $cot->fecha_emision?->format('d/m/Y') ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right">
                                            <a href="{{ route('proyectos.cotizacion', $proyecto) }}" class="text-sm text-gpt-600 hover:text-gpt-700">Ver</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="mt-2 text-sm text-slate-500">Sin cotizaciones registradas</p>
                        <a href="{{ route('proyectos.cotizacion', $proyecto) }}" class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                            Crear cotización
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Tab: Solicitudes --}}
    @if($activeTab === 'solicitudes')
        <div class="space-y-6">
            <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h3 class="text-base font-medium text-slate-900">Solicitudes internas</h3>
                    <p class="mt-0.5 text-sm text-slate-500">{{ $proyecto->solicitudesInterna->count() }} solicitud(es)</p>
                </div>
                @if($proyecto->solicitudesInterna->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tipo</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Código</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Solicitante</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Fecha solicitud</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Items</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($proyecto->solicitudesInterna as $sol)
                                    @php
                                        $solStatusColors = match($sol->estado) {
                                            'pendiente' => 'bg-amber-100 text-amber-800',
                                            'en_proceso' => 'bg-blue-100 text-blue-800',
                                            'respondida' => 'bg-green-100 text-green-800',
                                            'Cerrada' => 'bg-slate-100 text-slate-500',
                                            default => 'bg-slate-100 text-slate-700',
                                        };
                                        $solTypeLabel = match($sol->tipo) {
                                            'requisicion_compras' => 'Requisición de Compras',
                                            'orden_trabajo_ingenieria' => 'Orden de Trabajo Ing.',
                                            default => $sol->tipo,
                                        };
                                    @endphp
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-900">{{ $solTypeLabel }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm font-mono text-slate-600">{{ $sol->codigo_formato ?? $sol->cp_numero ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $solStatusColors }}">{{ ucfirst($sol->estado) }}</span>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-700">{{ $sol->solicitante->name ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500">{{ $sol->fecha_solicitud?->format('d/m/Y') ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-slate-700">{{ $sol->items->count() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.25 2.25 0 0113.5 2.25H15"/></svg>
                        <p class="mt-2 text-sm text-slate-500">Sin solicitudes internas registradas</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Tab: Historial de eventos --}}
    @if($activeTab === 'eventos')
        <div class="space-y-6">
            <div class="rounded-lg border border-slate-200 bg-white p-6">
                <h3 class="text-base font-medium text-slate-900 mb-4">Historial de eventos</h3>
                @if($proyecto->eventos->count() > 0)
                    <div class="flow-root">
                        <ul class="-mb-8">
                            @foreach($proyecto->eventos as $evento)
                                @php
                                    $eventoIcon = match($evento->tipo) {
                                        'cp_creado' => 'bg-blue-500',
                                        'cp_aprobado' => 'bg-green-500',
                                        'cp_rechazado' => 'bg-gpt-red-500',
                                        'equipo_asignado' => 'bg-gpt-600',
                                        'cambio_estado' => 'bg-amber-500',
                                        default => 'bg-slate-400',
                                    };
                                    $eventoLabel = match($evento->tipo) {
                                        'cp_creado' => 'CP creado',
                                        'cp_aprobado' => 'CP aprobado',
                                        'cp_rechazado' => 'CP rechazado',
                                        'equipo_asignado' => 'Equipo asignado',
                                        'cambio_estado' => 'Cambio de estado',
                                        default => ucfirst(str_replace('_', ' ', $evento->tipo)),
                                    };
                                @endphp
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="flex h-8 w-8 items-center justify-center rounded-full {{ $eventoIcon }} ring-4 ring-white">
                                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-slate-900">
                                                        <span class="font-medium">{{ $eventoLabel }}</span>
                                                        <span class="text-slate-500"> — {{ $evento->comentario ?? '' }}</span>
                                                    </p>
                                                    @if($evento->user)
                                                        <p class="mt-0.5 text-xs text-slate-500">Por {{ $evento->user->name }}</p>
                                                    @endif
                                                </div>
                                                <div class="whitespace-nowrap text-right text-xs text-slate-500">
                                                    {{ $evento->created_at?->format('d/m/Y H:i') ?? '' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="mt-2 text-sm text-slate-500">Sin eventos registrados</p>
                    </div>
                @endif
            </div>

            {{-- Historial de ponderación (snapshot mensual del %) --}}
            <div class="rounded-lg border border-slate-200 bg-white p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium text-slate-900">Historial de ponderación</h3>
                    @unlessrole('invitado')
                        <button type="button" wire:click="abrirPonderacionModal" class="text-sm font-medium text-gpt-600 hover:text-gpt-700 transition-colors">Actualizar</button>
                    @endunlessrole
                </div>
                @php
                    $mesesHist = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                    $colorHist = ['red' => '#ef4444', 'blue' => '#3b82f6', 'yellow' => '#eab308', 'green' => '#22c55e'];
                    $histTab = $proyecto->historialPonderacion->sortByDesc('id');
                @endphp
                @if($histTab->isNotEmpty())
                    <div class="flow-root">
                        <ul class="-mb-8">
                            @foreach($histTab as $h)
                                <li>
                                    <div class="relative pb-8">
                                        @if(!$loop->last)
                                            <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-slate-200" aria-hidden="true"></span>
                                        @endif
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="flex h-8 w-8 items-center justify-center rounded-full ring-4 ring-white" style="background: {{ $colorHist[$h->ponderacion->color ?? ''] ?? '#94a3b8' }}">
                                                    <span class="text-[10px] font-semibold text-white">{{ $h->ponderacion->porcentaje ?? '?' }}%</span>
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-slate-900">
                                                        <span class="font-medium">{{ $h->ponderacion->concepto ?? '—' }} ({{ $h->ponderacion->porcentaje ?? '?' }}%)</span>
                                                        <span class="text-slate-500"> — {{ $mesesHist[$h->mes - 1] ?? '?' }} {{ $h->anio }}</span>
                                                    </p>
                                                    @if($h->notas)
                                                        <p class="mt-0.5 text-xs text-slate-500">{{ $h->notas }}</p>
                                                    @endif
                                                    <p class="mt-0.5 text-xs text-slate-500">Por {{ $h->user?->name ?? 'Sistema' }}</p>
                                                </div>
                                                <div class="whitespace-nowrap text-right text-xs text-slate-500">
                                                    {{ $h->created_at?->format('d/m/Y H:i') ?? '' }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z"/></svg>
                        <p class="mt-2 text-sm text-slate-500">Sin snapshots de ponderación registrados</p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Approve CP Modal --}}
    @if($showAprobarModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50" wire:click.self="$set('showAprobarModal', false)">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 class="text-lg font-medium text-slate-900 mb-4">Aprobar CP</h3>
                <div class="space-y-4">
                    <div>
                        <label for="gerente_proyectos_id" class="block text-sm font-medium text-slate-700 mb-1">Gerente de Proyectos</label>
                        <select wire:model="gerente_proyectos_id" id="gerente_proyectos_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                            <option value="">Seleccionar...</option>
                            @foreach($equipoDisponible as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        @error('gerente_proyectos_id') <span class="text-xs text-gpt-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="notas_aprobar" class="block text-sm font-medium text-slate-700 mb-1">Notas (opcional)</label>
                        <textarea wire:model="notas_aprobar" id="notas_aprobar" rows="3" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-600" placeholder="Notas sobre la aprobación..."></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="$set('showAprobarModal', false)" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</button>
                    <button type="button" wire:click="aprobarCp" class="rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Aprobar</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Reject CP Modal --}}
    @if($showRechazarModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50" wire:click.self="$set('showRechazarModal', false)">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 class="text-lg font-medium text-slate-900 mb-4">Rechazar CP</h3>
                <div>
                    <label for="notas_rechazar" class="block text-sm font-medium text-slate-700 mb-1">Motivo del rechazo *</label>
                    <textarea wire:model="notas_rechazar" id="notas_rechazar" rows="4" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-600" placeholder="Explique el motivo del rechazo..."></textarea>
                    @error('notas_rechazar') <span class="text-xs text-gpt-red-600">{{ $message }}</span> @enderror
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="$set('showRechazarModal', false)" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</button>
                    <button type="button" wire:click="rechazarCp" class="rounded-lg bg-gpt-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-red-700">Rechazar</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Assign Team Modal --}}
    @if($showEquipoModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50" wire:click.self="$set('showEquipoModal', false)">
            <div class="w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl">
                <h3 class="text-lg font-medium text-slate-900 mb-4">Asignar equipo</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    @foreach([
                        ['label' => 'Director DN', 'key' => 'director_dn_id'],
                        ['label' => 'Gerente de Proyectos', 'key' => 'gerente_proyectos_id_equipo'],
                        ['label' => 'Gerente de Operaciones', 'key' => 'gerente_operaciones_id'],
                        ['label' => 'Ingeniero de Costos', 'key' => 'ingeniero_costos_id'],
                        ['label' => 'Ingeniero de Proyectos', 'key' => 'ingeniero_proyectos_id'],
                        ['label' => 'Trainee', 'key' => 'trainee_id'],
                    ] as $role)
                        <div>
                            <label for="{{ $role['key'] }}" class="block text-sm font-medium text-slate-700 mb-1">{{ $role['label'] }}</label>
                            <select wire:model="{{ $role['key'] }}" id="{{ $role['key'] }}" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <option value="">Sin asignar</option>
                                @foreach($equipoDisponible as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="$set('showEquipoModal', false)" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</button>
                    <button type="button" wire:click="asignarEquipo" class="rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-700">Guardar equipo</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Change Status Modal --}}
    @if($showEstadoModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50" wire:click.self="$set('showEstadoModal', false)">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 class="text-lg font-medium text-slate-900 mb-4">Cambiar estado</h3>
                <select wire:model="cambiar_estado" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600 mb-4">
                    @foreach(['en_revision' => 'En revisión', 'cotizando' => 'Cotizando', 'cotizado' => 'Cotizado', 'presentado' => 'Presentado', 'adjudicado_pendiente' => 'Adjudicado pend.', 'adjudicado_firmado' => 'Adjudicado', 'en_ejecucion' => 'En ejecución', 'en_cierre' => 'En cierre', 'cerrado' => 'Cerrado', 'cancelado' => 'Cancelado', 'perdido' => 'Perdido', 'archivado' => 'Archivado'] as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <textarea wire:model="cambiar_notas" rows="3" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-600 mb-4" placeholder="Notas sobre el cambio de estado..."></textarea>
                <div class="flex justify-end gap-3">
                    <button type="button" wire:click="$set('showEstadoModal', false)" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</button>
                    <form method="POST" action="{{ route('oportunidades.cambiar-estado', $proyecto) }}" class="inline">
                        @csrf
                        <input type="hidden" name="estado" value="{{ $cambiar_estado }}">
                        <input type="hidden" name="notas" value="{{ $cambiar_notas }}">
                        <button type="submit" class="rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-700">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- ───────────────────────── Modal: actualizar ponderación ───────────────────────── --}}
    @if($showPonderacionModal)
        @php
            $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            $historialMostrar = $proyecto->historialPonderacion()
                ->with('ponderacion', 'user')
                ->orderByDesc('anio')->orderByDesc('mes')
                ->limit(8)->get();
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50" wire:click.self="$set('showPonderacionModal', false)">
            <div class="w-full max-w-lg max-h-[90vh] flex flex-col rounded-xl bg-white shadow-2xl">
                <div class="flex items-start justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-semibold text-slate-900">Actualizar ponderación</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Registra un snapshot mensual para esta oportunidad.</p>
                    </div>
                    <button type="button" wire:click="$set('showPonderacionModal', false)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form wire:submit.prevent="guardarPonderacion" class="flex-1 overflow-y-auto px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Ponderación <span class="text-red-500">*</span></label>
                        <select wire:model="pond_ponderacion_id" class="block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-gpt-500 focus:outline-none focus:ring-1 focus:ring-gpt-500">
                            <option value="">— selecciona —</option>
                            @foreach($ponderacionesCatalogo as $p)
                                <option value="{{ $p->id }}">{{ $p->concepto }} ({{ $p->porcentaje }}%)</option>
                            @endforeach
                        </select>
                        @error('pond_ponderacion_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Mes <span class="text-red-500">*</span></label>
                            <select wire:model="pond_mes" class="block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-gpt-500 focus:outline-none focus:ring-1 focus:ring-gpt-500">
                                @foreach($meses as $idx => $nombreMes)
                                    <option value="{{ $idx + 1 }}">{{ $nombreMes }}</option>
                                @endforeach
                            </select>
                            @error('pond_mes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">Año <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="pond_anio" min="2020" max="2099" class="block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-gpt-500 focus:outline-none focus:ring-1 focus:ring-gpt-500">
                            @error('pond_anio') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Notas (opcional)</label>
                        <textarea wire:model="pond_notas" rows="2" class="block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-gpt-500 focus:outline-none focus:ring-1 focus:ring-gpt-500" placeholder="Motivo del cambio, contexto, etc."></textarea>
                        @error('pond_notas') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-start gap-2 rounded-lg bg-slate-50 px-3 py-2 text-[11px] text-slate-500">
                        <svg class="h-4 w-4 shrink-0 mt-0.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                        Si ya existe un snapshot para el mes/año seleccionado, se actualizará en lugar de duplicar.
                    </div>

                    @if($historialMostrar->isNotEmpty())
                        <div class="border-t border-slate-100 pt-4">
                            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400 mb-2">Historial reciente</p>
                            <div class="space-y-1.5 max-h-40 overflow-y-auto">
                                @foreach($historialMostrar as $h)
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="text-slate-600">{{ $meses[$h->mes - 1] ?? '?' }} {{ $h->anio }}</span>
                                        <span class="font-medium text-slate-800">{{ $h->ponderacion->concepto }} ({{ $h->ponderacion->porcentaje }}%)</span>
                                        <span class="text-slate-400 text-[10px]">{{ $h->user?->name ?? 'Sistema' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                        <button type="button" wire:click="$set('showPonderacionModal', false)" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700">Guardar snapshot</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>