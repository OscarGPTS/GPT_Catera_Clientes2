<div>
    {{-- Breadcrumb & Header --}}
    <div class="mb-2">
        <nav class="flex items-center gap-2 text-sm text-slate-500" aria-label="Breadcrumb">
            <a href="{{ route('proyectos.index') }}" class="hover:text-slate-700 transition-colors">Proyectos</a>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-slate-900">{{ $proyecto->cp_numero ?? 'CP-?' }}</span>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-slate-900">Minuta de Entrega</span>
        </nav>
        <div class="flex items-center gap-3">
            <h2 class="text-2xl font-medium text-slate-900">Minuta de Entrega</h2>
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $minutaObligatoria ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                {{ $minutaObligatoria ? 'Obligatorio' : 'Opcional' }}
            </span>
        </div>
        <p class="mt-1 text-sm text-slate-500">{{ $proyecto->cliente->razon_social ?? '—' }} — Acta de reunión de entrega</p>
    </div>

    {{-- Flash message --}}
    @if(session('success') || $successMessage)
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
             class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">
            {{ session('success') ?: $successMessage }}
        </div>
    @endif

    {{-- Already signed notice --}}
    @if($existingMinuta && $existingMinuta->status === 'firmada')
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4">
            <p class="text-sm font-medium text-green-800">Esta minuta ya fue firmada el {{ $existingMinuta->firmado_at->format('d/m/Y H:i') }}.</p>
        </div>
    @endif

    {{-- Stepper --}}
    <div class="rounded-lg border border-slate-200 bg-white p-6 mb-6">
        <nav aria-label="Progress" class="flex items-center justify-between">
            @foreach($steps as $index => $step)
                <div class="flex items-center flex-1 {{ $step['status'] === 'pending' ? 'opacity-50' : '' }}">
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold transition-all
                            {{ $step['status'] === 'completed' ? 'bg-gpt-600 text-white' : ($step['status'] === 'current' ? 'border-2 border-gpt-600 text-gpt-600 bg-white' : 'bg-slate-200 text-slate-500') }}">
                            @if($step['status'] === 'completed')
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </div>
                        <div class="hidden sm:block">
                            <span class="text-sm font-medium {{ ($step['status'] === 'current' || $step['status'] === 'completed') ? 'text-slate-900' : 'text-slate-400' }}">
                                {{ $step['label'] }}
                            </span>
                        </div>
                    </div>
                    @if($index < count($steps) - 1)
                        <div class="flex-1 mx-3 h-0.5 rounded transition-colors {{ $step['status'] === 'completed' ? 'bg-gpt-600' : 'bg-slate-200' }}"></div>
                    @endif
                </div>
            @endforeach
        </nav>
        <div class="text-center mt-4 sm:hidden">
            <span class="text-sm font-medium text-slate-900">{{ $steps[$currentStep]['label'] }}</span>
        </div>
    </div>

    {{-- Step Content --}}
    <div class="rounded-lg border border-slate-200 bg-white p-6 min-h-[400px]">

        {{-- Step 1: Datos básicos --}}
        @if($currentStep === 0)
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-slate-900 mb-2">Datos básicos de la reunión</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Fecha de reunión</label>
                        <input type="date" wire:model="fecha_reunion" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                        @error('fecha_reunion') <span class="text-xs text-gpt-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Hora inicio</label>
                            <input type="time" wire:model="hora_inicio" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                            @error('hora_inicio') <span class="text-xs text-gpt-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Hora fin</label>
                            <input type="time" wire:model="hora_fin" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                            @error('hora_fin') <span class="text-xs text-gpt-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Modalidad</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model="modalidad" value="presencial" class="h-4 w-4 text-gpt-600 border-slate-300 focus:ring-gpt-600">
                            <span class="text-sm text-slate-700">Presencial</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model="modalidad" value="virtual" class="h-4 w-4 text-gpt-600 border-slate-300 focus:ring-gpt-600">
                            <span class="text-sm text-slate-700">Virtual</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" wire:model="modalidad" value="mixta" class="h-4 w-4 text-gpt-600 border-slate-300 focus:ring-gpt-600">
                            <span class="text-sm text-slate-700">Mixta</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Proyecto</label>
                    <input type="text" readonly value="{{ $proyecto->cp_numero ?? $proyecto->tech_reference ?? 'Proyecto' }}" class="mt-1 block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 shadow-sm cursor-not-allowed">
                </div>
            </div>
        @endif

        {{-- Step 2: Orden del día --}}
        @if($currentStep === 1)
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-slate-900">Orden del día</h3>
                    <button type="button" wire:click="agregarPunto" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Agregar punto
                    </button>
                </div>
                <p class="text-sm text-slate-500">Define los puntos a tratar durante la reunión de entrega.</p>
                <div class="space-y-2">
                    @foreach($ordenDia as $index => $punto)
                        <div class="flex items-start gap-2 group" wire:key="punto-{{ $index }}">
                            <span class="mt-2 text-sm font-medium text-slate-400 w-6 text-right">{{ $index + 1 }}</span>
                            <input type="text" wire:model="ordenDia.{{ $index }}.descripcion" class="flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600" placeholder="Descripción del punto a tratar...">
                            @if(count($ordenDia) > 1)
                                <button type="button" wire:click="eliminarPunto({{ $index }})" class="mt-2 p-1 text-slate-400 hover:text-gpt-red-600 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Step 3: Acuerdos --}}
        @if($currentStep === 2)
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-slate-900">Acuerdos</h3>
                    <button type="button" wire:click="agregarAcuerdo" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Agregar acuerdo
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 w-16">N°</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Descripción</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 w-56">Responsable</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 w-44">Fecha compromiso</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 w-12"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($acuerdos as $index => $acuerdo)
                                <tr class="hover:bg-slate-50 transition-colors group" wire:key="acuerdo-{{ $index }}">
                                    <td class="px-4 py-2 text-sm text-slate-500">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2">
                                        <input type="text" wire:model="acuerdos.{{ $index }}.descripcion" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600" placeholder="Descripción del acuerdo...">
                                    </td>
                                    <td class="px-4 py-2">
                                        <select wire:model="acuerdos.{{ $index }}.responsable" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                            <option value="">Seleccionar...</option>
                                            @foreach($this->usuarios as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="date" wire:model="acuerdos.{{ $index }}.fechaCompromiso" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        @if(count($acuerdos) > 1)
                                            <button type="button" wire:click="eliminarAcuerdo({{ $index }})" class="p-1 text-slate-400 hover:text-gpt-red-600 transition-colors">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Step 4: Participantes --}}
        @if($currentStep === 3)
            <div class="space-y-4">
                <h3 class="text-lg font-medium text-slate-900">Participantes</h3>
                <p class="text-sm text-slate-500">Selecciona los usuarios que participarán en la reunión y asigna su rol.</p>
                @error('participantesSeleccionados') <span class="text-xs text-gpt-red-600">{{ $message }}</span> @enderror
                <div class="space-y-3">
                    @foreach($this->usuarios as $u)
                        <div class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 hover:bg-slate-50 transition-colors" wire:key="user-{{ $u->id }}">
                            <input type="checkbox" value="{{ $u->id }}" wire:model="participantesSeleccionados" class="h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-900">{{ $u->name }}</p>
                                <p class="text-xs text-slate-400">{{ $u->email }}</p>
                            </div>
                            <select wire:model="rolesParticipantes.{{ $u->id }}" class="w-40 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <option value="">Rol en reunión</option>
                                <option value="organizador">Organizador</option>
                                <option value="expositor">Expositor</option>
                                <option value="asistente">Asistente</option>
                                <option value="cliente">Cliente</option>
                                <option value="testigo">Testigo</option>
                            </select>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Step 5: Preview & Firma --}}
        @if($currentStep === 4)
            <div class="space-y-6">
                <h3 class="text-lg font-medium text-slate-900">Vista previa y firma</h3>
                <div class="rounded-lg border border-slate-100 bg-slate-50 p-6 space-y-5">
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900 mb-2">Datos de la reunión</h4>
                        <dl class="grid grid-cols-2 gap-2 text-sm">
                            <dt class="text-slate-500">Fecha:</dt>
                            <dd class="text-slate-900">{{ $fecha_reunion ?: '—' }}</dd>
                            <dt class="text-slate-500">Horario:</dt>
                            <dd class="text-slate-900">{{ $hora_inicio ?: '—' }} a {{ $hora_fin ?: '—' }}</dd>
                            <dt class="text-slate-500">Modalidad:</dt>
                            <dd class="text-slate-900 capitalize">{{ $modalidad ?: '—' }}</dd>
                            <dt class="text-slate-500">Proyecto:</dt>
                            <dd class="text-slate-900">{{ $proyecto->cp_numero ?? $proyecto->tech_reference ?? '—' }}</dd>
                        </dl>
                    </div>
                    <div class="border-t border-slate-200 pt-4">
                        <h4 class="text-sm font-semibold text-slate-900 mb-2">Orden del día</h4>
                        <ol class="list-decimal list-inside space-y-1 text-sm">
                            @foreach(collect($ordenDia)->filter(fn($p) => !empty($p['descripcion'])) as $punto)
                                <li class="text-slate-900">{{ $punto['descripcion'] }}</li>
                            @empty
                                <li class="text-slate-400">Sin puntos definidos</li>
                            @endforeach
                        </ol>
                    </div>
                    <div class="border-t border-slate-200 pt-4">
                        <h4 class="text-sm font-semibold text-slate-900 mb-2">Acuerdos</h4>
                        @php $acuerdosConDescripcion = collect($acuerdos)->filter(fn($a) => !empty($a['descripcion'])); @endphp
                        @if($acuerdosConDescripcion->count() > 0)
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200">
                                        <th class="pb-2 text-left text-xs font-semibold text-slate-500">N°</th>
                                        <th class="pb-2 text-left text-xs font-semibold text-slate-500">Descripción</th>
                                        <th class="pb-2 text-left text-xs font-semibold text-slate-500">Responsable</th>
                                        <th class="pb-2 text-left text-xs font-semibold text-slate-500">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($acuerdosConDescripcion as $index => $acuerdo)
                                        <tr class="border-b border-slate-100">
                                            <td class="py-2 text-slate-500">{{ $index + 1 }}</td>
                                            <td class="py-2 text-slate-900">{{ $acuerdo['descripcion'] }}</td>
                                            <td class="py-2 text-slate-900">{{ $this->usuariosMap[$acuerdo['responsable']] ?? '—' }}</td>
                                            <td class="py-2 text-slate-900">{{ $acuerdo['fechaCompromiso'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-sm text-slate-400">Sin acuerdos registrados</p>
                        @endif
                    </div>
                    <div class="border-t border-slate-200 pt-4">
                        <h4 class="text-sm font-semibold text-slate-900 mb-2">Participantes</h4>
                        <ul class="space-y-1 text-sm">
                            @foreach($participantesSeleccionados as $id)
                                <li class="text-slate-900">
                                    {{ $this->usuariosMap[$id] ?? 'Usuario #' . $id }}
                                    <span class="text-slate-400 ml-1">({{ $rolesParticipantes[$id] ?? 'Sin rol' }})</span>
                                </li>
                            @empty
                                <li class="text-slate-400">Sin participantes seleccionados</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="guardarBorrador" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Guardar borrador
                    </button>
                    @can('firmar minuta')
                        <button type="button" wire:click="firmarYemitir" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Firmar y emitir
                        </button>
                    @endcan
                </div>
            </div>
        @endif
    </div>

    {{-- Navigation buttons --}}
    <div class="flex items-center justify-between mt-6">
        @if($currentStep > 0)
            <button type="button" wire:click="previousStep" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Anterior
            </button>
        @else
            <div></div>
        @endif
        <div></div>
        @if($currentStep < count($steps) - 1)
            <button type="button" wire:click="nextStep" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                Siguiente
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        @endif
    </div>
</div>