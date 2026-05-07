<x-layouts.app>
    <div class="space-y-6" x-data="bitacoraApp()">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <h2 class="text-2xl font-medium text-slate-900">Bitácora Diaria</h2>
                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600">FO-GPT-PYT-01-D</span>
                </div>
                <p class="mt-1 text-sm text-slate-500">Registro diario de actividades en sitio</p>
            </div>
        </div>

        {{-- Top controls --}}
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div class="flex flex-wrap items-end gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Proyecto</label>
                        <select x-model="proyectoId" class="mt-1 block w-full min-w-[240px] rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                            <option value="">Seleccionar proyecto</option>
                            @foreach($proyectos ?? [] as $p)
                                <option value="{{ $p->id ?? $p['id'] }}">{{ $p->cp ?? $p['cp'] }} - {{ $p->nombre ?? $p['nombre'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Fecha</label>
                        <input type="date" x-model="fecha" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                    </div>
                </div>
            </div>
        </div>

        {{-- View toggle --}}
        <div class="flex rounded-lg border border-slate-200 bg-slate-100 p-1">
            <button @click="vista = 'nueva'" :class="vista === 'nueva' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700'" class="flex-1 rounded-md px-4 py-2 text-sm font-medium transition-colors">
                Nueva bitácora
            </button>
            <button @click="vista = 'historial'" :class="vista === 'historial' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-500 hover:text-slate-700'" class="flex-1 rounded-md px-4 py-2 text-sm font-medium transition-colors">
                Historial
            </button>
        </div>

        {{-- =================== NUEVA BITÁCORA =================== --}}
        <div x-show="vista === 'nueva'" class="space-y-6">
            {{-- Stepper nav --}}
            <div class="rounded-lg border border-slate-200 bg-white p-4 sm:p-6">
                <nav class="flex flex-wrap items-center gap-1 sm:gap-2" aria-label="Progress">
                    @foreach(['Personal en sitio', 'Equipos en sitio', 'Actividades del día', 'V°B° del cliente', 'Enviar'] as $i => $label)
                        <button type="button" @click="irPaso({{ $i }})" class="group flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium transition-colors"
                            :class="paso === {{ $i }} ? 'bg-gpt-600 text-white' : (paso > {{ $i }} ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500')">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold"
                                :class="paso === {{ $i }} ? 'bg-white text-gpt-600' : (paso > {{ $i }} ? 'bg-emerald-500 text-white' : 'bg-slate-300 text-white')">
                                <template x-if="paso > {{ $i }}">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </template>
                                <template x-if="paso <= {{ $i }}">
                                    <span>{{ $i + 1 }}</span>
                                </template>
                            </span>
                            <span class="hidden sm:inline">{{ $label }}</span>
                        </button>
                        @if($i < 4)
                            <span class="hidden h-px w-4 bg-slate-200 sm:inline" :class="paso > {{ $i }} ? 'bg-emerald-400' : ''"></span>
                        @endif
                    @endforeach
                </nav>
            </div>

            {{-- Step 1: Personal en sitio --}}
            <div x-show="paso === 0" class="rounded-lg border border-slate-200 bg-white p-4 sm:p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-slate-900">Personal en sitio</h3>
                    <p class="mt-1 text-sm text-slate-500">Confirma el personal presente y registra horas trabajadas</p>
                </div>

                <div class="space-y-3">
                    @forelse($personal_asignado ?? [] as $idx => $miembro)
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
                                    <input type="checkbox" x-model="personal[{{ $idx }}].presente" class="h-5 w-5 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                                    <span class="text-sm text-slate-700">Presente hoy</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <label class="text-sm text-slate-500">Horas</label>
                                    <input type="number" x-model="personal[{{ $idx }}].horas" min="0" max="24" class="w-16 rounded-lg border border-slate-200 px-2 py-1.5 text-sm text-slate-900 focus:border-gpt-600 focus:ring-gpt-600" placeholder="8">
                                </div>
                                <label class="flex items-center gap-2 cursor-pointer min-h-[48px]">
                                    <input type="checkbox" x-model="personal[{{ $idx }}].extra" class="h-5 w-5 rounded border-slate-300 text-amber-600 focus:ring-amber-600">
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
                        <button type="button" @click="agregarSubcontratista()" class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors min-h-[48px]">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Agregar
                        </button>
                    </div>
                    <template x-for="(sub, i) in subcontratistas" :key="i">
                        <div class="flex flex-col gap-2 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-3 sm:flex-row sm:items-center mb-3">
                            <input type="text" x-model="sub.nombre" placeholder="Nombre del trabajador" class="flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-600">
                            <input type="text" x-model="sub.empresa" placeholder="Empresa" class="w-40 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-600">
                            <input type="number" x-model="sub.horas" placeholder="Horas" class="w-20 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:ring-gpt-600">
                            <button type="button" @click="subcontratistas.splice(i, 1)" class="rounded-lg p-2 text-slate-400 hover:bg-gpt-red-50 hover:text-gpt-red-600 transition-colors min-h-[48px] min-w-[48px]">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>

                {{-- Summary --}}
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="text-sm text-slate-600">
                        <span class="font-medium text-slate-900" x-text="personal.filter(p => p.presente).length + subcontratistas.length"></span> personas en sitio ·
                        <span class="font-medium text-slate-900" x-text="totalHoras"></span> horas totales
                    </p>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-200 pt-6">
                    <button type="button" @click="irPaso(1)" class="rounded-lg bg-gpt-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors min-h-[48px]">
                        Siguiente → Equipos
                    </button>
                </div>
            </div>

            {{-- Step 2: Equipos en sitio --}}
            <div x-show="paso === 1" class="rounded-lg border border-slate-200 bg-white p-4 sm:p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-slate-900">Equipos en sitio</h3>
                    <p class="mt-1 text-sm text-slate-500">Registra el estado y horómetro de los equipos</p>
                </div>

                <div class="space-y-3">
                    @forelse($equipos_boe ?? [] as $idx => $equipo)
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
                                    <input type="checkbox" x-model="equipos[{{ $idx }}].enSitio" class="h-5 w-5 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                                    <span class="text-sm text-slate-700">En sitio</span>
                                </label>
                                <div class="flex items-center gap-2">
                                    <label class="text-sm text-slate-500">Horómetro</label>
                                    <input type="text" x-model="equipos[{{ $idx }}].horometro" class="w-28 rounded-lg border border-slate-200 px-2 py-1.5 text-sm text-slate-900 focus:border-gpt-600 focus:ring-gpt-600" placeholder="0000 h">
                                </div>
                                <select x-model="equipos[{{ $idx }}].estado" class="rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 focus:border-gpt-600 focus:ring-gpt-600 min-h-[48px]">
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
                    <button type="button" @click="irPaso(0)" class="rounded-lg border border-slate-200 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors min-h-[48px]">
                        ← Personal
                    </button>
                    <button type="button" @click="irPaso(2)" class="rounded-lg bg-gpt-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors min-h-[48px]">
                        Siguiente → Actividades
                    </button>
                </div>
            </div>

            {{-- Step 3: Actividades del día --}}
            <div x-show="paso === 2" class="rounded-lg border border-slate-200 bg-white p-4 sm:p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-slate-900">Actividades del día</h3>
                    <p class="mt-1 text-sm text-slate-500">Describe las actividades realizadas y registra desviaciones</p>
                </div>

                {{-- Quick shortcuts --}}
                <div class="flex flex-wrap gap-2">
                    <button type="button" @click="insertarShortcut('/soldadura')" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors min-h-[48px]">/soldadura</button>
                    <button type="button" @click="insertarShortcut('/htap')" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors min-h-[48px]">/htap</button>
                    <button type="button" @click="insertarShortcut('/pruebas')" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors min-h-[48px]">/pruebas</button>
                    <button type="button" @click="insertarShortcut('/movilizacion')" class="rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50 transition-colors min-h-[48px]">/movilización</button>
                </div>

                {{-- Textarea --}}
                <textarea x-model="actividades" rows="6" class="block w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 shadow-sm focus:border-gpt-600 focus:ring-gpt-600" placeholder="Describe las actividades realizadas hoy..."></textarea>

                {{-- Photo capture --}}
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <button type="button" @click="capturarFoto()" class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors min-h-[48px]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                            Tomar foto
                        </button>
                        <input type="file" accept="image/*" capture="environment" multiple class="hidden" x-ref="fotoInput" @change="onFotoCapturada">
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="(foto, i) in fotos" :key="i">
                            <div class="relative h-20 w-20 overflow-hidden rounded-lg border border-slate-200 bg-slate-100">
                                <img :src="foto.url" class="h-full w-full object-cover" alt="Foto de bitácora">
                                <button type="button" @click="fotos.splice(i, 1)" class="absolute right-0.5 top-0.5 rounded-full bg-slate-900/60 p-0.5 text-white hover:bg-slate-900/80">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Desviación toggle --}}
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <label class="flex items-center gap-3 cursor-pointer min-h-[48px]">
                        <input type="checkbox" x-model="desviacion.activa" class="h-5 w-5 rounded border-slate-300 text-gpt-red-600 focus:ring-gpt-red-600">
                        <span class="text-sm font-medium text-slate-900">Reportar desviación</span>
                    </label>

                    <div x-show="desviacion.activa" x-cloak class="mt-4 space-y-3">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Tipo</label>
                                <select x-model="desviacion.tipo" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-gpt-red-600 focus:ring-gpt-red-600 min-h-[48px]">
                                    <option value="">Seleccionar</option>
                                    <option value="tecnica">Técnica</option>
                                    <option value="seguridad">Seguridad</option>
                                    <option value="calidad">Calidad</option>
                                    <option value="cliente">Cliente</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Severidad</label>
                                <select x-model="desviacion.severidad" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-gpt-red-600 focus:ring-gpt-red-600 min-h-[48px]">
                                    <option value="">Seleccionar</option>
                                    <option value="baja">Baja</option>
                                    <option value="media">Media</option>
                                    <option value="alta">Alta</option>
                                </select>
                            </div>
                        </div>
                        <textarea x-model="desviacion.descripcion" rows="3" class="block w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-gpt-red-600 focus:ring-gpt-red-600" placeholder="Describe la desviación..."></textarea>
                    </div>
                </div>

                <div class="flex justify-between gap-3 border-t border-slate-200 pt-6">
                    <button type="button" @click="irPaso(1)" class="rounded-lg border border-slate-200 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors min-h-[48px]">
                        ← Equipos
                    </button>
                    <button type="button" @click="irPaso(3)" class="rounded-lg bg-gpt-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors min-h-[48px]">
                        Siguiente → V°B°
                    </button>
                </div>
            </div>

            {{-- Step 4: V°B° del cliente --}}
            <div x-show="paso === 3" class="rounded-lg border border-slate-200 bg-white p-4 sm:p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-slate-900">V°B° del cliente</h3>
                    <p class="mt-1 text-sm text-slate-500">Registra la validación del cliente en sitio</p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Nombre</label>
                        <input type="text" x-model="vobo.nombre" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600" placeholder="Nombre del representante">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Organización</label>
                        <input type="text" x-model="vobo.organizacion" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600" placeholder="Empresa / organización">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Fecha</label>
                        <input type="date" x-model="vobo.fecha" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                    </div>
                </div>

                {{-- Signature area --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Firma del cliente</label>
                    <div class="flex h-48 items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50">
                        <div class="text-center">
                            <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            <p class="mt-2 text-sm text-slate-400">Firma del cliente aquí</p>
                        </div>
                    </div>
                    <button type="button" class="mt-3 inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors min-h-[48px]">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                        Tomar foto de firma
                    </button>
                </div>

                <div class="flex justify-between gap-3 border-t border-slate-200 pt-6">
                    <button type="button" @click="irPaso(2)" class="rounded-lg border border-slate-200 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors min-h-[48px]">
                        ← Actividades
                    </button>
                    <button type="button" @click="irPaso(4)" class="rounded-lg bg-gpt-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors min-h-[48px]">
                        Siguiente → Enviar
                    </button>
                </div>
            </div>

            {{-- Step 5: Enviar --}}
            <div x-show="paso === 4" class="rounded-lg border border-slate-200 bg-white p-4 sm:p-6 space-y-6">
                <div>
                    <h3 class="text-lg font-medium text-slate-900">Enviar bitácora</h3>
                    <p class="mt-1 text-sm text-slate-500">Revisa la información antes de enviar</p>
                </div>

                {{-- Summary --}}
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 space-y-4">
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Personal en sitio</h4>
                        <p class="mt-1 text-sm text-slate-900">
                            <span x-text="personal.filter(p => p.presente).length"></span> personas ·
                            <span x-text="totalHoras"></span> horas
                        </p>
                        <template x-for="p in personal.filter(p => p.presente)" :key="p.nombre">
                            <span class="inline-block mr-2 text-xs text-slate-600" x-text="p.nombre + ' (' + p.horas + 'h)' + (p.extra ? ' Extra' : '')"></span>
                        </template>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Equipos</h4>
                        <p class="mt-1 text-sm text-slate-900">
                            <span x-text="equipos.filter(e => e.enSitio).length"></span> equipos en sitio
                        </p>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Actividades</h4>
                        <p class="mt-1 text-sm text-slate-900 whitespace-pre-wrap" x-text="actividades || 'Sin actividades registradas'"></p>
                    </div>
                    <div x-show="desviacion.activa">
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-gpt-red-500">Desviación reportada</h4>
                        <p class="mt-1 text-sm text-gpt-red-700" x-text="desviacion.tipo + ' / ' + desviacion.severidad"></p>
                    </div>
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">V°B° cliente</h4>
                        <p class="mt-1 text-sm text-slate-900" x-text="vobo.nombre + ' — ' + vobo.organizacion"></p>
                    </div>
                </div>

                <label class="flex items-start gap-3 cursor-pointer min-h-[48px]">
                    <input type="checkbox" x-model="confirmado" class="mt-0.5 h-5 w-5 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                    <span class="text-sm text-slate-700">Confirmo que la información es correcta</span>
                </label>

                <div class="flex flex-col gap-3 sm:flex-row sm:justify-between border-t border-slate-200 pt-6">
                    <button type="button" @click="guardarBorrador()" class="rounded-lg border border-slate-200 bg-white px-6 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors min-h-[48px]">
                        Guardar borrador
                    </button>
                    <button type="button" @click="firmarEnviar()" :disabled="!confirmado" class="rounded-lg bg-gpt-600 px-8 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed min-h-[48px]">
                        Firmar y enviar
                    </button>
                </div>
            </div>
        </div>

        {{-- =================== HISTORIAL =================== --}}
        <div x-show="vista === 'historial'" class="space-y-4">
            @forelse($bitacoras ?? [] as $bitacora)
                @php
                    $statusColor = ($bitacora['estado'] ?? '') === 'firmada' ? 'border-emerald-200 bg-emerald-50' : 'border-slate-200 bg-white';
                    $statusBadge = ($bitacora['estado'] ?? '') === 'firmada' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600';
                    $tieneDesviacion = !empty($bitacora['desviacion']);
                @endphp
                <div class="rounded-lg border {{ $statusColor }} p-4 cursor-pointer hover:shadow-sm transition-shadow" @click="abrirDetalle({{ $loop->index }})">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-slate-100 px-3 py-2 text-center">
                                <p class="text-xs font-semibold uppercase text-slate-500">{{ \Carbon\Carbon::parse($bitacora['fecha'] ?? now())->format('M') }}</p>
                                <p class="text-lg font-bold text-slate-900">{{ \Carbon\Carbon::parse($bitacora['fecha'] ?? now())->format('d') }}</p>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-medium text-slate-900">Bitácora #{{ $bitacora['numero'] ?? $loop->iteration }}</p>
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
                        <svg class="h-5 w-5 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </div>
            @empty
                <div class="rounded-lg border border-slate-200 bg-white p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="mt-4 text-lg font-medium text-slate-900">Aún no capturas la bitácora de hoy</h3>
                    <p class="mt-1 text-sm text-slate-500">Las bitácoras deben registrarse diariamente.</p>
                    <button type="button" @click="vista = 'nueva'" class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors min-h-[48px]">
                        Iniciar bitácora
                    </button>
                </div>
            @endforelse
        </div>

        {{-- =================== MODAL DE DETALLE =================== --}}
        <div x-show="modalAbierto" x-cloak class="fixed inset-0 z-50 overflow-y-auto" x-transition.opacity.duration.200ms>
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/30" @click="modalAbierto = false"></div>
                <div class="relative w-full max-w-lg rounded-lg bg-white shadow-xl" x-show="modalAbierto" x-transition>
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <h3 class="text-lg font-medium text-slate-900">Detalle de bitácora</h3>
                        <button @click="modalAbierto = false" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors min-h-[48px] min-w-[48px]">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="p-6 space-y-4">
                        <p class="text-sm text-slate-600">Contenido completo de la bitácora seleccionada.</p>
                    </div>
                    <div class="border-t border-slate-200 px-6 py-4">
                        <button @click="modalAbierto = false" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors min-h-[48px]">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('bitacoraApp', () => ({
        vista: 'nueva',
        proyectoId: '{{ $proyecto_seleccionado ?? '' }}',
        fecha: '{{ $fecha ?? date('Y-m-d') }}',
        paso: 0,
        confirmado: false,
        modalAbierto: false,

        personal: @json($personal_asignado ?? []).map(p => ({
            ...p,
            presente: true,
            horas: 8,
            extra: false,
        })),
        subcontratistas: [],

        equipos: @json($equipos_boe ?? []).map(e => ({
            ...e,
            enSitio: true,
            horometro: e.horometro ?? '',
            estado: 'operativo',
        })),

        actividades: '',
        fotos: [],

        desviacion: {
            activa: false,
            tipo: '',
            severidad: '',
            descripcion: '',
        },

        vobo: {
            nombre: '',
            organizacion: '',
            fecha: '{{ date('Y-m-d') }}',
        },

        get totalHoras() {
            let suma = this.personal.filter(p => p.presente).reduce((s, p) => s + (parseInt(p.horas) || 0), 0);
            suma += this.subcontratistas.reduce((s, sub) => s + (parseInt(sub.horas) || 0), 0);
            return suma;
        },

        irPaso(n) {
            this.paso = n;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        agregarSubcontratista() {
            this.subcontratistas.push({ nombre: '', empresa: '', horas: 8 });
        },

        insertarShortcut(texto) {
            this.actividades += (this.actividades ? '\n' : '') + texto + ' ';
        },

        capturarFoto() {
            this.$refs.fotoInput.click();
        },

        onFotoCapturada(e) {
            const files = e.target.files;
            for (let i = 0; i < files.length; i++) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    this.fotos.push({ url: ev.target.result, file: files[i] });
                };
                reader.readAsDataURL(files[i]);
            }
            e.target.value = '';
        },

        guardarBorrador() {
            alert('Bitácora guardada como borrador.');
        },

        firmarEnviar() {
            if (!this.confirmado) return;
            alert('Bitácora firmada y enviada.');
            this.vista = 'historial';
        },

        abrirDetalle(idx) {
            this.modalAbierto = true;
        },
    }));
});
</script>