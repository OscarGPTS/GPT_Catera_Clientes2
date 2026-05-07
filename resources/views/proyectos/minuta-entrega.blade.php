<x-layouts.app>
    <x-slot name="header">
        <div>
            <nav class="flex items-center gap-2 text-sm text-slate-500 mb-2" aria-label="Breadcrumb">
                <a href="{{ route('proyectos.index') }}" class="hover:text-slate-700 transition-colors">Proyectos</a>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="font-medium text-slate-900">{{ $proyecto->dn ?? $proyecto->nombre ?? 'DN-001' }}</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="font-medium text-slate-900">Minuta de Entrega</span>
            </nav>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-medium text-slate-900">Minuta de Entrega</h2>
                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ ($esObligatorio ?? true) ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-700' }}">
                    {{ ($esObligatorio ?? true) ? 'Obligatorio' : 'Opcional' }}
                </span>
            </div>
            <p class="mt-1 text-sm text-slate-500">{{ $proyecto->nombre ?? 'Proyecto' }} — Acta de reunión de entrega</p>
        </div>
    </x-slot>

    <div x-data="minutaWizard()" x-init="init()">
        {{-- Stepper --}}
        <div class="rounded-lg border border-slate-200 bg-white p-6 mb-6">
            <nav aria-label="Progress" class="flex items-center justify-between">
                <template x-for="(step, index) in steps" :key="index">
                    <div class="flex items-center flex-1" :class="{ 'opacity-50': step.status === 'pending' }">
                        <div class="flex items-center gap-2">
                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold transition-all"
                                :class="{
                                    'bg-gpt-600 text-white': step.status === 'completed',
                                    'border-2 border-gpt-600 text-gpt-600 bg-white': step.status === 'current',
                                    'bg-slate-200 text-slate-500': step.status === 'pending'
                                }"
                            >
                                <template x-if="step.status === 'completed'">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </template>
                                <template x-if="step.status !== 'completed'">
                                    <span x-text="index + 1"></span>
                                </template>
                            </div>
                            <div class="hidden sm:block">
                                <span
                                    class="text-sm font-medium"
                                    :class="{
                                        'text-slate-900': step.status === 'current' || step.status === 'completed',
                                        'text-slate-400': step.status === 'pending'
                                    }"
                                    x-text="step.label"
                                ></span>
                            </div>
                        </div>
                        <div
                            x-show="index < steps.length - 1"
                            class="flex-1 mx-3 h-0.5 rounded transition-colors"
                            :class="{
                                'bg-gpt-600': step.status === 'completed',
                                'bg-slate-200': step.status !== 'completed'
                            }"
                        ></div>
                    </div>
                </template>
            </nav>

            {{-- Mobile step label --}}
            <div class="text-center mt-4 sm:hidden">
                <span class="text-sm font-medium text-slate-900" x-text="steps[currentStep].label"></span>
            </div>
        </div>

        {{-- Step Content --}}
        <div class="rounded-lg border border-slate-200 bg-white p-6 min-h-[400px]">

            {{-- Step 1: Datos básicos --}}
            <div x-show="currentStep === 0" class="space-y-6">
                <h3 class="text-lg font-medium text-slate-900 mb-2">Datos básicos de la reunión</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Fecha de reunión</label>
                        <input type="date" x-model="form.fechaReunion" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Hora inicio</label>
                            <input type="time" x-model="form.horaInicio" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700">Hora fin</label>
                            <input type="time" x-model="form.horaFin" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Modalidad</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" x-model="form.modalidad" value="presencial" class="h-4 w-4 text-gpt-600 border-slate-300 focus:ring-gpt-600">
                            <span class="text-sm text-slate-700">Presencial</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" x-model="form.modalidad" value="virtual" class="h-4 w-4 text-gpt-600 border-slate-300 focus:ring-gpt-600">
                            <span class="text-sm text-slate-700">Virtual</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" x-model="form.modalidad" value="mixta" class="h-4 w-4 text-gpt-600 border-slate-300 focus:ring-gpt-600">
                            <span class="text-sm text-slate-700">Mixta</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700">Proyecto</label>
                    <input type="text" readonly x-model="form.proyecto" class="mt-1 block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 shadow-sm cursor-not-allowed">
                </div>
            </div>

            {{-- Step 2: Orden del día --}}
            <div x-show="currentStep === 1" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-slate-900">Orden del día</h3>
                    <button type="button" @click="agregarPunto()" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Agregar punto
                    </button>
                </div>

                <p class="text-sm text-slate-500">Define los puntos a tratar durante la reunión de entrega.</p>

                <div class="space-y-2">
                    <template x-for="(punto, index) in form.ordenDia" :key="index">
                        <div class="flex items-start gap-2 group">
                            <span class="mt-2 text-sm font-medium text-slate-400 w-6 text-right" x-text="index + 1"></span>
                            <input type="text" x-model="punto.descripcion" class="flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600" placeholder="Descripción del punto a tratar...">
                            <button type="button" @click="eliminarPunto(index)" class="mt-2 p-1 text-slate-400 hover:text-gpt-red-600 transition-colors opacity-0 group-hover:opacity-100" x-show="form.ordenDia.length > 1">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Step 3: Acuerdos --}}
            <div x-show="currentStep === 2" class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-slate-900">Acuerdos</h3>
                    <button type="button" @click="agregarAcuerdo()" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
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
                            <template x-for="(acuerdo, index) in form.acuerdos" :key="index">
                                <tr class="hover:bg-slate-50 transition-colors group">
                                    <td class="px-4 py-2 text-sm text-slate-500" x-text="index + 1"></td>
                                    <td class="px-4 py-2">
                                        <input type="text" x-model="acuerdo.descripcion" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600" placeholder="Descripción del acuerdo...">
                                    </td>
                                    <td class="px-4 py-2">
                                        <select x-model="acuerdo.responsable" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                            <option value="">Seleccionar...</option>
                                            @foreach($usuarios ?? [] as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="date" x-model="acuerdo.fechaCompromiso" class="w-full rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <button type="button" @click="eliminarAcuerdo(index)" class="p-1 text-slate-400 hover:text-gpt-red-600 transition-colors opacity-0 group-hover:opacity-100" x-show="form.acuerdos.length > 1">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Step 4: Participantes --}}
            <div x-show="currentStep === 3" class="space-y-4">
                <h3 class="text-lg font-medium text-slate-900">Participantes</h3>
                <p class="text-sm text-slate-500">Selecciona los usuarios que participarán en la reunión y asigna su rol.</p>

                <div class="space-y-3">
                    @forelse($usuarios ?? [] as $u)
                        <div class="flex items-center gap-3 rounded-lg border border-slate-200 p-3 hover:bg-slate-50 transition-colors">
                            <input type="checkbox"
                                   :value="{{ $u->id }}"
                                   x-model="form.participantesSeleccionados"
                                   class="h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-900">{{ $u->name }}</p>
                                <p class="text-xs text-slate-400">{{ $u->email }}</p>
                            </div>
                            <select x-model="form.rolesParticipantes[{{ $u->id }}]" class="w-40 rounded-lg border border-slate-200 bg-white px-2 py-1.5 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <option value="">Rol en reunión</option>
                                <option value="organizador">Organizador</option>
                                <option value="expositor">Expositor</option>
                                <option value="asistente">Asistente</option>
                                <option value="cliente">Cliente</option>
                                <option value="testigo">Testigo</option>
                            </select>
                        </div>
                    @empty
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-8 text-center">
                            <p class="text-sm text-slate-500">No hay usuarios disponibles. Contacta al administrador.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Step 5: Preview & Firma --}}
            <div x-show="currentStep === 4" class="space-y-6">
                <h3 class="text-lg font-medium text-slate-900">Vista previa y firma</h3>

                <div class="rounded-lg border border-slate-100 bg-slate-50 p-6 space-y-5">
                    {{-- Datos básicos --}}
                    <div>
                        <h4 class="text-sm font-semibold text-slate-900 mb-2">Datos de la reunión</h4>
                        <dl class="grid grid-cols-2 gap-2 text-sm">
                            <dt class="text-slate-500">Fecha:</dt>
                            <dd class="text-slate-900" x-text="form.fechaReunion || '—'"></dd>
                            <dt class="text-slate-500">Horario:</dt>
                            <dd class="text-slate-900" x-text="(form.horaInicio || '—') + ' a ' + (form.horaFin || '—')"></dd>
                            <dt class="text-slate-500">Modalidad:</dt>
                            <dd class="text-slate-900 capitalize" x-text="form.modalidad || '—'"></dd>
                            <dt class="text-slate-500">Proyecto:</dt>
                            <dd class="text-slate-900" x-text="form.proyecto || '—'"></dd>
                        </dl>
                    </div>

                    <div class="border-t border-slate-200 pt-4">
                        <h4 class="text-sm font-semibold text-slate-900 mb-2">Orden del día</h4>
                        <ol class="list-decimal list-inside space-y-1 text-sm">
                            <template x-for="(punto, index) in form.ordenDia.filter(p => p.descripcion)" :key="index">
                                <li class="text-slate-900" x-text="punto.descripcion"></li>
                            </template>
                            <li x-show="form.ordenDia.filter(p => p.descripcion).length === 0" class="text-slate-400">Sin puntos definidos</li>
                        </ol>
                    </div>

                    <div class="border-t border-slate-200 pt-4">
                        <h4 class="text-sm font-semibold text-slate-900 mb-2">Acuerdos</h4>
                        <template x-if="form.acuerdos.filter(a => a.descripcion).length > 0">
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
                                    <template x-for="(acuerdo, index) in form.acuerdos.filter(a => a.descripcion)" :key="index">
                                        <tr class="border-b border-slate-100">
                                            <td class="py-2 text-slate-500" x-text="index + 1"></td>
                                            <td class="py-2 text-slate-900" x-text="acuerdo.descripcion"></td>
                                            <td class="py-2 text-slate-900" x-text="getNombreResponsable(acuerdo.responsable)"></td>
                                            <td class="py-2 text-slate-900" x-text="acuerdo.fechaCompromiso || '—'"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </template>
                        <p x-show="form.acuerdos.filter(a => a.descripcion).length === 0" class="text-sm text-slate-400">Sin acuerdos registrados</p>
                    </div>

                    <div class="border-t border-slate-200 pt-4">
                        <h4 class="text-sm font-semibold text-slate-900 mb-2">Participantes</h4>
                        <ul class="space-y-1 text-sm">
                            <template x-for="id in form.participantesSeleccionados" :key="id">
                                <li class="text-slate-900">
                                    <span x-text="getNombreParticipante(id)"></span>
                                    <span class="text-slate-400 ml-1" x-text="'(' + (form.rolesParticipantes[id] || 'Sin rol') + ')'"></span>
                                </li>
                            </template>
                            <li x-show="form.participantesSeleccionados.length === 0" class="text-slate-400">Sin participantes seleccionados</li>
                        </ul>
                    </div>
                </div>

                {{-- Firmar y emitir --}}
                <div class="flex items-center gap-3">
                    <button type="button" @click="guardarBorrador()" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Guardar borrador
                    </button>
                    <button type="button" @click="firmarYemitir()" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Firmar y emitir
                    </button>
                </div>
            </div>
        </div>

        {{-- Navigation buttons --}}
        <div class="flex items-center justify-between mt-6">
            <button type="button"
                    x-show="currentStep > 0"
                    @click="previousStep()"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Anterior
            </button>
            <div></div>
            <button type="button"
                    x-show="currentStep < steps.length - 1"
                    @click="nextStep()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                Siguiente
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    @push('scripts')
    <script>
        function minutaWizard() {
            return {
                currentStep: 0,
                steps: [
                    { label: 'Datos básicos', status: 'current' },
                    { label: 'Orden del día', status: 'pending' },
                    { label: 'Acuerdos', status: 'pending' },
                    { label: 'Participantes', status: 'pending' },
                    { label: 'Preview & firma', status: 'pending' },
                ],
                form: {
                    fechaReunion: '',
                    horaInicio: '',
                    horaFin: '',
                    modalidad: 'presencial',
                    proyecto: '{{ $proyecto->dn ?? $proyecto->nombre ?? 'Proyecto' }}',
                    ordenDia: [{ descripcion: '' }],
                    acuerdos: [{ descripcion: '', responsable: '', fechaCompromiso: '' }],
                    participantesSeleccionados: [],
                    rolesParticipantes: {}
                },
                usuariosMap: @json(collect($usuarios ?? [])->mapWithKeys(fn($u) => [$u->id => $u->name])),

                init() {
                    this.updateSteps();
                },

                updateSteps() {
                    for (var i = 0; i < this.steps.length; i++) {
                        if (i < this.currentStep) this.steps[i].status = 'completed';
                        else if (i === this.currentStep) this.steps[i].status = 'current';
                        else this.steps[i].status = 'pending';
                    }
                },

                nextStep() {
                    if (this.currentStep < this.steps.length - 1) {
                        this.currentStep++;
                        this.updateSteps();
                    }
                },

                previousStep() {
                    if (this.currentStep > 0) {
                        this.currentStep--;
                        this.updateSteps();
                    }
                },

                agregarPunto() {
                    this.form.ordenDia.push({ descripcion: '' });
                },

                eliminarPunto(index) {
                    if (this.form.ordenDia.length > 1) {
                        this.form.ordenDia.splice(index, 1);
                    }
                },

                agregarAcuerdo() {
                    this.form.acuerdos.push({ descripcion: '', responsable: '', fechaCompromiso: '' });
                },

                eliminarAcuerdo(index) {
                    if (this.form.acuerdos.length > 1) {
                        this.form.acuerdos.splice(index, 1);
                    }
                },

                getNombreResponsable(id) {
                    return this.usuariosMap[id] || '—';
                },

                getNombreParticipante(id) {
                    return this.usuariosMap[id] || 'Usuario #' + id;
                },

                guardarBorrador() {
                    alert('Borrador guardado (simulación).');
                },

                firmarYemitir() {
                    alert('Minuta firmada y emitida (simulación).');
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
