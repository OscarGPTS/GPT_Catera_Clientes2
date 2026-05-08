<div>
    @section('title', 'Bitácora Diaria')

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-medium text-slate-900">Bitácora Diaria</h2>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">FO-GPT-PYT-01-D</span>
                </div>
                <p class="mt-1 text-sm text-slate-500">Registro diario de actividades en sitio</p>
            </div>
        </div>

        @if($successMessage)
            <div wire:transition.opacity.duration.500ms class="rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800">
                {{ $successMessage }}
            </div>
        @endif

        @if($errorMessage)
            <div wire:transition.opacity.duration.500ms class="rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-800">
                {{ $errorMessage }}
            </div>
        @endif

        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Proyecto</label>
                        <select wire:model.live="proyectoId" class="mt-1 block w-full min-w-[240px] rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                            <option value="">Seleccionar proyecto</option>
                            @foreach($proyectos as $p)
                                <option value="{{ $p['id'] }}">{{ $p['cp'] }} - {{ $p['nombre'] }}</option>
                            @endforeach
                        </select>
                        @error('proyectoId') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Fecha</label>
                        <input type="date" wire:model="fecha" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                        @error('fecha') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="flex rounded-lg border border-slate-200 bg-slate-100 p-1">
            <button wire:click="$set('vista', 'nueva')" class="flex-1 rounded-md px-4 py-2 text-sm font-medium transition-colors {{ $vista === 'nueva' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700' }}">
                Nueva bitácora
            </button>
            <button wire:click="$set('vista', 'historial')" class="flex-1 rounded-md px-4 py-2 text-sm font-medium transition-colors {{ $vista === 'historial' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700' }}">
                Historial
            </button>
        </div>

        @if($vista === 'nueva')
            @if(!$proyectoId)
                <div class="rounded-lg border border-slate-200 bg-white p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="mt-4 text-lg font-medium text-slate-900">Selecciona un proyecto</h3>
                    <p class="mt-1 text-sm text-slate-500">Elige un proyecto activo para registrar la bitácora diaria.</p>
                </div>
            @else

            {{-- Stepper nav --}}
            <div class="rounded-lg border border-slate-200 bg-white p-4 sm:p-6">
                <nav class="flex flex-wrap items-center gap-1 sm:gap-2" aria-label="Progress">
                    @foreach(['Personal en sitio', 'Equipos en sitio', 'Actividades del día', 'V°B° del cliente', 'Enviar'] as $i => $label)
                        <button type="button" wire:click="irPaso({{ $i }})" class="group flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition-colors {{ $paso === $i ? 'bg-gpt-600 text-white' : ($paso > $i ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500') }}">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold {{ $paso === $i ? 'bg-white text-gpt-600' : ($paso > $i ? 'bg-emerald-500 text-white' : 'bg-slate-300 text-white') }}">
                                @if($paso > $i)
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </span>
                            <span class="hidden sm:inline">{{ $label }}</span>
                        </button>
                        @if($i < 4)
                            <span class="hidden h-px w-4 sm:inline {{ $paso > $i ? 'bg-emerald-400' : 'bg-slate-200' }}"></span>
                        @endif
                    @endforeach
                </nav>
            </div>

            {{-- Step 1: Personal en sitio --}}
            @if($paso === 0)
                <div class="rounded-lg border border-slate-200 bg-white p-4 sm:p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-slate-900">Personal en sitio</h3>
                        <p class="mt-1 text-sm text-slate-500">Confirma el personal presente y registra horas trabajadas</p>
                    </div>

                    <div class="space-y-3">
                        @forelse($personal as $idx => $miembro)
                            <div class="flex flex-col gap-3 rounded-lg border border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-full bg-gpt-100 text-sm font-semibold text-gpt-700">
                                        {{ strtoupper(substr($miembro['nombre'] ?? '', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">{{ $miembro['nombre'] ?? '—' }}</p>
                                        <p class="text-xs text-slate-500">{{ $miembro['puesto'] ?? '—' }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <label class="flex items-center gap-2 cursor-pointer min-h-[48px]">
                                        <input type="checkbox" wire:model="personal.{{ $idx }}.presente" class="h-5 w-5 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                                        <span class="text-sm text-slate-700">Presente hoy</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <label class="text-sm text-slate-500">Horas</label>
                                        <input type="number" wire:model="personal.{{ $idx }}.horas" min="0" max="24" class="w-16 rounded-lg border border-slate-200 px-2 py-1.5 text-sm text-slate-900 focus:border-gpt-600 focus:ring-gpt-600" placeholder="8">
                                    </div>
                                    <label class="flex items-center gap-2 cursor-pointer min-h-[48px]">
                                        <input type="checkbox" wire:model="personal.{{ $idx }}.extra" class="h-5 w-5 rounded border-slate-300 text-amber-600 focus:ring-amber-600">
                                        <span class="text-sm text-slate-700">Extra</span>
                                    </label>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400 py-8 text-center">No hay personal asignado al proyecto. Asigna personal primero.</p>
                        @endforelse
                    </div>

                    {{-- Subcontratistas --}}
                    <div class="border-t border-slate-200 pt-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Subcontratistas</h4>
                            <button type="button" wire:click="agregarSubcontratista" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors min-h-[48px]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                Agregar
                            </button>
                        </div>
                        @foreach($subcontratistas as $i => $sub)
                            <div class="flex flex-col gap-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-3 sm:flex-row sm:items-center mb-3">
                                <input type="text" wire:model="subcontratistas.{{ $i }}.nombre" placeholder="Nombre del trabajador" class="flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <input type="text" wire:model="subcontratistas.{{ $i }}.empresa" placeholder="Empresa" class="w-40 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <input type="number" wire:model="subcontratistas.{{ $i }}.horas" placeholder="Horas" class="w-20 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <button type="button" wire:click="removerSubcontratista({{ $i }})" class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-600 transition-colors min-h-[48px] min-w-[48px]">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>

                    {{-- Summary --}}
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm text-slate-600">
                            <span class="font-medium text-slate-900">{{ $this->totalPresentes }}</span> personas en sitio ·
                            <span class="font-medium text-slate-900">{{ $this->totalHoras }}</span> horas totales
                        </p>
                    </div>

                    <div class="flex justify-end gap-3 border-t border-slate-200 pt-6">
                        <button type="button" wire:click="irPaso(1)" class="rounded-lg bg-gpt-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors min-h-[48px]">
                            Siguiente → Equipos
                        </button>
                    </div>
                </div>
            @endif

            {{-- Step 2: Equipos en sitio --}}
            @if($paso === 1)
                <div class="rounded-lg border border-slate-200 bg-white p-4 sm:p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-slate-900">Equipos en sitio</h3>
                        <p class="mt-1 text-sm text-slate-500">Registra el estado y horómetro de los equipos</p>
                    </div>

                    <div class="space-y-3">
                        @forelse($equipos as $idx => $equipo)
                            <div class="flex flex-col gap-3 rounded-lg border border-slate-200 p-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h3m-3 3h3m-3 3h3m4 3.75V6a2.25 2.25 0 00-2.25-2.25H7.5A2.25 2.25 0 005.25 6v12A2.25 2.25 0 007.5 20.25h9A2.25 2.25 0 0018.75 18v-2.25"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">{{ $equipo['nombre'] ?? '—' }}</p>
                                        <p class="text-xs text-slate-500">{{ $equipo['tipo'] ?? '—' }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-3">
                                    <label class="flex items-center gap-2 cursor-pointer min-h-[48px]">
                                        <input type="checkbox" wire:model="equipos.{{ $idx }}.enSitio" class="h-5 w-5 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                                        <span class="text-sm text-slate-700">En sitio</span>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <label class="text-sm text-slate-500">Horómetro</label>
                                        <input type="text" wire:model="equipos.{{ $idx }}.horometro" class="w-28 rounded-lg border border-slate-200 px-2 py-1.5 text-sm text-slate-900 focus:border-gpt-600 focus:ring-gpt-600" placeholder="0000 h">
                                    </div>
                                    <select wire:model="equipos.{{ $idx }}.estado" class="rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 focus:border-gpt-600 focus:ring-gpt-600 min-h-[48px]">
                                        <option value="operativo">Operativo</option>
                                        <option value="falla">Falla</option>
                                        <option value="mantenimiento">Mantenimiento</option>
                                        <option value="standby">Stand by</option>
                                    </select>
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-400 py-8 text-center">No hay equipos registrados en el BOE de este proyecto.</p>
                        @endforelse
                    </div>

                    <div class="flex justify-between gap-3 border-t border-slate-200 pt-6">
                        <button type="button" wire:click="irPaso(0)" class="rounded-lg border border-slate-200 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors min-h-[48px]">
                            ← Personal
                        </button>
                        <button type="button" wire:click="irPaso(2)" class="rounded-lg bg-gpt-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors min-h-[48px]">
                            Siguiente → Actividades
                        </button>
                    </div>
                </div>
            @endif

            {{-- Step 3: Actividades del día --}}
            @if($paso === 2)
                <div class="rounded-lg border border-slate-200 bg-white p-4 sm:p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-slate-900">Actividades del día</h3>
                        <p class="mt-1 text-sm text-slate-500">Describe las actividades realizadas y registra desviaciones</p>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @foreach(['/soldadura', '/htap', '/pruebas', '/movilizacion'] as $shortcut)
                            <button type="button" wire:click="insertarShortcut('{{ $shortcut }}')" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors min-h-[48px]">{{ $shortcut }}</button>
                        @endforeach
                    </div>

                    <textarea wire:model="actividades" rows="6" class="block w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm focus:border-gpt-600 focus:ring-gpt-600" placeholder="Describe las actividades realizadas hoy..."></textarea>
                    @error('actividades') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror

                    {{-- Desviación toggle --}}
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <label class="flex items-center gap-3 cursor-pointer min-h-[48px]">
                            <input type="checkbox" wire:model="desviacion.activa" class="h-5 w-5 rounded border-slate-300 text-gpt-red-600 focus:ring-gpt-red-600">
                            <span class="text-sm font-medium text-slate-900">Reportar desviación</span>
                        </label>

                        @if($desviacion['activa'])
                            <div class="mt-4 space-y-3">
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Tipo</label>
                                        <select wire:model="desviacion.tipo" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-gpt-red-600 focus:ring-gpt-red-600 min-h-[48px]">
                                            <option value="">Seleccionar</option>
                                            <option value="tecnica">Técnica</option>
                                            <option value="seguridad">Seguridad</option>
                                            <option value="calidad">Calidad</option>
                                            <option value="cliente">Cliente</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Severidad</label>
                                        <select wire:model="desviacion.severidad" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-gpt-red-600 focus:ring-gpt-red-600 min-h-[48px]">
                                            <option value="">Seleccionar</option>
                                            <option value="baja">Baja</option>
                                            <option value="media">Media</option>
                                            <option value="alta">Alta</option>
                                        </select>
                                    </div>
                                </div>
                                <textarea wire:model="desviacion.descripcion" rows="3" class="block w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-gpt-red-600 focus:ring-gpt-red-600" placeholder="Describe la desviación..."></textarea>
                            </div>
                        @endif
                    </div>

                    <div class="flex justify-between gap-3 border-t border-slate-200 pt-6">
                        <button type="button" wire:click="irPaso(1)" class="rounded-lg border border-slate-200 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors min-h-[48px]">
                            ← Equipos
                        </button>
                        <button type="button" wire:click="irPaso(3)" class="rounded-lg bg-gpt-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors min-h-[48px]">
                            Siguiente → V°B°
                        </button>
                    </div>
                </div>
            @endif

            {{-- Step 4: V°B° del cliente --}}
            @if($paso === 3)
                <div class="rounded-lg border border-slate-200 bg-white p-4 sm:p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-slate-900">V°B° del cliente</h3>
                        <p class="mt-1 text-sm text-slate-500">Registra la validación del cliente en sitio</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Nombre</label>
                            <input type="text" wire:model="vobo.nombre" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600" placeholder="Nombre del representante">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Organización</label>
                            <input type="text" wire:model="vobo.organizacion" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600" placeholder="Empresa / organización">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Fecha</label>
                            <input type="date" wire:model="vobo.fecha" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                        </div>
                    </div>

                    <div class="flex justify-between gap-3 border-t border-slate-200 pt-6">
                        <button type="button" wire:click="irPaso(2)" class="rounded-lg border border-slate-200 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors min-h-[48px]">
                            ← Actividades
                        </button>
                        <button type="button" wire:click="irPaso(4)" class="rounded-lg bg-gpt-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors min-h-[48px]">
                            Siguiente → Enviar
                        </button>
                    </div>
                </div>
            @endif

            {{-- Step 5: Enviar --}}
            @if($paso === 4)
                <div class="rounded-lg border border-slate-200 bg-white p-4 sm:p-6 space-y-6">
                    <div>
                        <h3 class="text-lg font-medium text-slate-900">Enviar bitácora</h3>
                        <p class="mt-1 text-sm text-slate-500">Revisa la información antes de enviar</p>
                    </div>

                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 space-y-4">
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Personal en sitio</h4>
                            <p class="mt-1 text-sm text-slate-900">{{ $this->totalPresentes }} personas · {{ $this->totalHoras }} horas</p>
                            @foreach(collect($personal)->filter(fn($p) => $p['presente']) as $p)
                                <span class="inline-block mr-2 text-xs text-slate-600">{{ $p['nombre'] }} ({{ $p['horas'] }}h){{ $p['extra'] ? ' Extra' : '' }}</span>
                            @endforeach
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Equipos</h4>
                            <p class="mt-1 text-sm text-slate-900">{{ $this->totalEquiposSitio }} equipos en sitio</p>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Actividades</h4>
                            <p class="mt-1 text-sm text-slate-900 whitespace-pre-wrap">{{ $actividades ?: 'Sin actividades registradas' }}</p>
                        </div>
                        @if($desviacion['activa'])
                            <div>
                                <h4 class="text-xs font-semibold uppercase tracking-wider text-red-500">Desviación reportada</h4>
                                <p class="mt-1 text-sm text-red-700">{{ $desviacion['tipo'] }} / {{ $desviacion['severidad'] }}</p>
                            </div>
                        @endif
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">V°B° cliente</h4>
                            <p class="mt-1 text-sm text-slate-900">{{ $vobo['nombre'] }} — {{ $vobo['organizacion'] }}</p>
                        </div>
                    </div>

                    <label class="flex items-start gap-3 cursor-pointer min-h-[48px]">
                        <input type="checkbox" wire:model="confirmado" class="mt-0.5 h-5 w-5 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                        <span class="text-sm text-slate-700">Confirmo que la información es correcta</span>
                    </label>

                    <div class="flex flex-col gap-3 sm:flex-row sm:justify-between border-t border-slate-200 pt-6">
                        <button type="button" wire:click="guardarBorrador" class="rounded-lg border border-slate-200 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors min-h-[48px]">
                            Guardar borrador
                        </button>
                        <button type="button" wire:click="firmarEnviar" {{ !$confirmado ? 'disabled' : '' }} class="rounded-lg bg-gpt-600 px-8 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed min-h-[48px]">
                            Firmar y enviar
                        </button>
                    </div>
                </div>
            @endif

            @endif {{-- end if proyectoId --}}
        @endif {{-- end if vista === 'nueva' --}}

        @if($vista === 'historial')
            <div class="space-y-4">
                @if(!$proyectoId)
                    <div class="rounded-lg border border-slate-200 bg-white p-12 text-center">
                        <h3 class="text-lg font-medium text-slate-900">Selecciona un proyecto</h3>
                        <p class="mt-1 text-sm text-slate-500">Elige un proyecto para ver el historial de bitácoras.</p>
                    </div>
                @else
                    @forelse($bitacoras as $bitacora)
                        @php
                            $statusColor = ($bitacora['estado'] ?? '') === 'firmada' ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-white';
                            $statusBadge = ($bitacora['estado'] ?? '') === 'firmada' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600';
                            $tieneDesviacion = $bitacora['tiene_desviacion'] ?? false;
                        @endphp
                        <div class="rounded-lg border {{ $statusColor }} p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-lg bg-slate-100 px-3 py-2 text-center">
                                        <p class="text-xs font-semibold uppercase text-slate-500">{{ \Carbon\Carbon::parse($bitacora['fecha'])->format('M') }}</p>
                                        <p class="text-lg font-bold text-slate-900">{{ \Carbon\Carbon::parse($bitacora['fecha'])->format('d') }}</p>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-medium text-slate-900">Bitácora #{{ $bitacora['numero'] }}</p>
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusBadge }}">
                                                {{ ($bitacora['estado'] ?? '') === 'firmada' ? 'Firmada' : 'Borrador' }}
                                            </span>
                                            @if($tieneDesviacion)
                                                <span class="relative flex h-2.5 w-2.5">
                                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-gpt-red-400 opacity-75"></span>
                                                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-gpt-red-500"></span>
                                                </span>
                                                <span class="text-xs font-medium text-gpt-red-600">Desviación reportada</span>
                                            @endif
                                        </div>
                                        <p class="mt-1 text-xs text-slate-500 line-clamp-1">{{ \Illuminate\Support\Str::limit($bitacora['actividades'] ?? '', 100) }}</p>
                                        <p class="mt-0.5 text-xs text-slate-400">{{ $bitacora['responsable'] ?? '—' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-slate-200 bg-white p-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <h3 class="mt-4 text-lg font-medium text-slate-900">Aún no capturas la bitácora de hoy</h3>
                            <p class="mt-1 text-sm text-slate-500">Las bitácoras deben registrarse diariamente.</p>
                            <button type="button" wire:click="$set('vista', 'nueva')" class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors min-h-[48px]">
                                Iniciar bitácora
                            </button>
                        </div>
                    @endforelse
                @endif
            </div>
        @endif
    </div>
</div>