<x-layouts.app>
@section('title', 'Viáticos')
<div x-data="{
    tab: 'pendientes',
    newModalOpen: false,
    newStep: 1,
    newForm: {
        proyecto_id: '',
        fecha_inicio: '',
        fecha_fin: '',
        justificacion: '',
        lugar: '',
        centro_costo: '',
        personal: [],
        partidas: [
            { concepto: 'Hospedaje', dias: 0, monto_estimado: 0, tasa: 1200 },
            { concepto: 'Alimentos', dias: 0, monto_estimado: 0, tasa: 600 },
            { concepto: 'Transporte aéreo', dias: 0, monto_estimado: 0, tasa: 0 },
            { concepto: 'Transporte terrestre', dias: 0, monto_estimado: 0, tasa: 0 },
            { concepto: 'Transporte local', dias: 0, monto_estimado: 0, tasa: 0 },
            { concepto: 'Otros', dias: 0, monto_estimado: 0, tasa: 0 },
        ],
        adjuntos: []
    },
    detailOpen: false,
    detailSolicitud: null,
    detailTab: 'info',
    rechazoMotivo: '',
    showRechazoTextarea: false,
    calcDiasPeriodo() {
        if (!this.newForm.fecha_inicio || !this.newForm.fecha_fin) return 0;
        const start = new Date(this.newForm.fecha_inicio);
        const end = new Date(this.newForm.fecha_fin);
        const diff = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;
        return diff > 0 ? diff : 0;
    },
    get totalPersonas() {
        return this.newForm.personal.length;
    },
    get totalMonto() {
        return this.newForm.partidas.reduce((sum, p) => sum + (parseFloat(p.monto_estimado) || 0), 0);
    },
    openNewModal() {
        this.newForm = {
            proyecto_id: '',
            fecha_inicio: '',
            fecha_fin: '',
            justificacion: '',
            lugar: '',
            centro_costo: '',
            personal: [],
            partidas: [
                { concepto: 'Hospedaje', dias: 0, monto_estimado: 0, tasa: 1200 },
                { concepto: 'Alimentos', dias: 0, monto_estimado: 0, tasa: 600 },
                { concepto: 'Transporte aéreo', dias: 0, monto_estimado: 0, tasa: 0 },
                { concepto: 'Transporte terrestre', dias: 0, monto_estimado: 0, tasa: 0 },
                { concepto: 'Transporte local', dias: 0, monto_estimado: 0, tasa: 0 },
                { concepto: 'Otros', dias: 0, monto_estimado: 0, tasa: 0 },
            ],
            adjuntos: []
        };
        this.newStep = 1;
        this.newModalOpen = true;
    },
    closeNewModal() {
        this.newModalOpen = false;
    },
    openDetail(solicitud) {
        this.detailSolicitud = solicitud;
        this.detailTab = 'info';
        this.showRechazoTextarea = false;
        this.rechazoMotivo = '';
        this.detailOpen = true;
    },
    closeDetail() {
        this.detailOpen = false;
        this.detailSolicitud = null;
    },
    nextStep() { if (this.newStep < 4) this.newStep++; },
    prevStep() { if (this.newStep > 1) this.newStep--; },
    submitSolicitud() {
        this.closeNewModal();
    }
}" x-on:keydown.escape.window="closeNewModal(); closeDetail();">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-medium text-slate-900">Viáticos</h2>
            <p class="mt-1 text-sm text-slate-500">FO-GPT-SSGG-01-A &middot; Gastos de viaje y viáticos</p>
        </div>
        <x-button variant="primary" x-on:click="openNewModal()">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Nueva solicitud
        </x-button>
    </div>

    <div class="mt-6 border-b border-slate-200">
        <nav class="-mb-px flex space-x-6">
            <button @@click="tab = 'pendientes'"
                :class="tab === 'pendientes' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors">
                Pendientes
                <span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $pendientesCount ?? 0 }}</span>
            </button>
            <button @@click="tab = 'aprobadas'"
                :class="tab === 'aprobadas' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors">
                Aprobadas
            </button>
            <button @@click="tab = 'rechazadas'"
                :class="tab === 'rechazadas' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors">
                Rechazadas
            </button>
            <button @@click="tab = 'todas'"
                :class="tab === 'todas' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors">
                Todas
            </button>
        </nav>
    </div>

    <div class="mt-6 space-y-4">
        @forelse($solicitudes ?? [] as $solicitud)
            @php
                $estadoBadge = match($solicitud->estado ?? '') {
                    'borrador' => ['label' => 'Borrador', 'class' => 'bg-slate-100 text-slate-700'],
                    'en_aprobacion_n1' => ['label' => 'En aprobación N1', 'class' => 'bg-blue-100 text-blue-800'],
                    'en_aprobacion_n2' => ['label' => 'En aprobación N2', 'class' => 'bg-blue-100 text-blue-800'],
                    'aprobada' => ['label' => 'Aprobada', 'class' => 'bg-green-100 text-green-800'],
                    'rechazada' => ['label' => 'Rechazada', 'class' => 'bg-gpt-red-100 text-gpt-red-800'],
                    default => ['label' => $solicitud->estado ?? '—', 'class' => 'bg-slate-100 text-slate-700'],
                };
            @endphp
            <x-slot name="solicitudLoop">
                <div class="rounded-lg border border-slate-200 bg-white p-5 hover:border-slate-300 hover:shadow-sm transition-all cursor-pointer"
                     @@click="openDetail({{ $solicitud->id ?? 0 }})">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold text-slate-900">{{ $solicitud->codigo ?? 'VTC-2025-018' }}</span>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estadoBadge['class'] }}">
                                {{ $estadoBadge['label'] }}
                            </span>
                        </div>
                        <span class="text-sm font-semibold text-slate-900">$ {{ number_format($solicitud->total ?? 12500, 0) }} MXN</span>
                    </div>

                    <div class="mt-3 flex items-center gap-6 text-sm text-slate-600">
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                            {{ \Carbon\Carbon::parse($solicitud->fecha_inicio ?? '2025-02-28')->format('d-m') }}
                            &rarr;
                            {{ \Carbon\Carbon::parse($solicitud->fecha_fin ?? '2025-03-06')->format('d-m') }}
                            ({{ \Carbon\Carbon::parse($solicitud->fecha_inicio ?? '2025-02-28')->year }})
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/></svg>
                            {{ $solicitud->solicitante->name ?? 'Solicitante' }}
                        </span>
                    </div>

                    <div class="mt-3 flex items-center gap-4">
                        <span class="text-xs font-medium uppercase text-slate-500">Personal:</span>
                        @php $personal = $solicitud->personal ?? collect([]); @endphp
                        <div class="flex -space-x-1.5">
                            @foreach($personal->take(5) as $p)
                                <div class="flex h-7 w-7 items-center justify-center rounded-full border border-white bg-gpt-600 text-[10px] font-semibold text-white" title="{{ $p->name ?? '' }}">
                                    {{ strtoupper(substr($p->name ?? 'U', 0, 2)) }}
                                </div>
                            @endforeach
                            @if($personal->count() > 5)
                                <div class="flex h-7 w-7 items-center justify-center rounded-full border border-white bg-slate-200 text-[10px] font-medium text-slate-600">
                                    +{{ $personal->count() - 5 }}
                                </div>
                            @endif
                        </div>
                    </div>

                    <p class="mt-3 text-sm text-slate-600 truncate">
                        {{ $solicitud->justificacion ?? 'Movilización de equipo de Hot Tap a Texmelucan' }}
                    </p>

                    <div class="mt-4 flex items-center gap-1">
                        @php
                            $pasos = [
                                ['label' => 'Solicitante', 'completo' => true],
                                ['label' => 'Servs Generales', 'completo' => in_array($solicitud->estado ?? '', ['en_aprobacion_n2', 'aprobada'])],
                                ['label' => 'Dirección', 'completo' => ($solicitud->estado ?? '') === 'aprobada'],
                            ];
                        @endphp
                        @foreach($pasos as $i => $paso)
                            <div class="flex items-center gap-1">
                                <div class="flex items-center justify-center h-5 w-5 rounded-full text-[10px] font-semibold {{ $paso['completo'] ? 'bg-gpt-600 text-white' : 'bg-slate-200 text-slate-500' }}">
                                    @if($paso['completo'])
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </div>
                                @if($i < count($pasos) - 1)
                                    <div class="h-0.5 w-8 {{ $paso['completo'] ? 'bg-gpt-600' : 'bg-slate-200' }}"></div>
                                @endif
                            </div>
                        @endforeach
                        <span class="ml-3 text-[11px] text-slate-400">N1 SG &rarr; N2 DG</span>
                    </div>

                    @if(($solicitud->estado ?? '') === 'rechazada')
                        <div class="mt-3 rounded-md bg-gpt-red-50 px-3 py-2">
                            <p class="text-sm font-medium text-gpt-red-700">Motivo: {{ $solicitud->motivo_rechazo ?? 'No especificado' }}</p>
                        </div>
                    @endif
                </div>
            </x-slot>
        @empty
            <div class="rounded-lg border border-slate-200 bg-white p-12">
                <x-empty-state
                    title="Sin viáticos registrados"
                    description="No se encontraron solicitudes de viáticos. Registra una nueva solicitud para comenzar."
                />
            </div>
        @endforelse
    </div>

    <div x-show="newModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-slate-900/50" @@click="closeNewModal()" x-transition.opacity></div>

            <div class="relative z-50 w-full max-w-3xl rounded-xl border border-slate-200 bg-white shadow-xl" @@click.stop x-transition>
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-medium text-slate-900" id="modal-title">Nueva solicitud de viáticos</h2>
                        <p class="text-xs text-slate-500">Sección {{ newStep }} de 4</p>
                    </div>
                    <button @@click="closeNewModal()" class="rounded-lg p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex border-b border-slate-200">
                    <button @@click="newStep = 1" :class="newStep >= 1 ? 'text-gpt-600 border-gpt-600' : 'text-slate-400 border-transparent'" class="flex-1 border-b-2 px-4 py-3 text-center text-sm font-medium transition-colors">
                        <span class="mr-1.5 inline-flex h-5 w-5 items-center justify-center rounded-full text-xs font-semibold" :class="newStep >= 1 ? 'bg-gpt-600 text-white' : 'bg-slate-200 text-slate-500'">1</span>
                        Datos generales
                    </button>
                    <button @@click="newStep = 2" :class="newStep >= 2 ? 'text-gpt-600 border-gpt-600' : 'text-slate-400 border-transparent'" class="flex-1 border-b-2 px-4 py-3 text-center text-sm font-medium transition-colors">
                        <span class="mr-1.5 inline-flex h-5 w-5 items-center justify-center rounded-full text-xs font-semibold" :class="newStep >= 2 ? 'bg-gpt-600 text-white' : 'bg-slate-200 text-slate-500'">2</span>
                        Personal
                    </button>
                    <button @@click="newStep = 3" :class="newStep >= 3 ? 'text-gpt-600 border-gpt-600' : 'text-slate-400 border-transparent'" class="flex-1 border-b-2 px-4 py-3 text-center text-sm font-medium transition-colors">
                        <span class="mr-1.5 inline-flex h-5 w-5 items-center justify-center rounded-full text-xs font-semibold" :class="newStep >= 3 ? 'bg-gpt-600 text-white' : 'bg-slate-200 text-slate-500'">3</span>
                        Partidas
                    </button>
                    <button @@click="newStep = 4" :class="newStep >= 4 ? 'text-gpt-600 border-gpt-600' : 'text-slate-400 border-transparent'" class="flex-1 border-b-2 px-4 py-3 text-center text-sm font-medium transition-colors">
                        <span class="mr-1.5 inline-flex h-5 w-5 items-center justify-center rounded-full text-xs font-semibold" :class="newStep >= 4 ? 'bg-gpt-600 text-white' : 'bg-slate-200 text-slate-500'">4</span>
                        Adjuntos
                    </button>
                </div>

                <div class="max-h-[60vh] overflow-y-auto p-6">
                    <div x-show="newStep === 1">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Proyecto</label>
                                <select x-model="newForm.proyecto_id" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                    <option value="">Seleccionar proyecto...</option>
                                    @foreach($proyectos ?? [] as $p)
                                        <option value="{{ $p->id }}">{{ $p->cp ?? '' }} - {{ $p->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Fecha inicio</label>
                                    <input type="date" x-model="newForm.fecha_inicio" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Fecha fin</label>
                                    <input type="date" x-model="newForm.fecha_fin" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                </div>
                            </div>
                            <div x-show="calcDiasPeriodo() > 0" class="rounded-md bg-gpt-50 px-3 py-2">
                                <p class="text-sm text-gpt-700">Período: <span class="font-semibold" x-text="calcDiasPeriodo()"></span> días</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Justificación</label>
                                <textarea x-model="newForm.justificacion" rows="3" placeholder="Describa el motivo del viaje..." class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-600"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Lugar</label>
                                <input type="text" x-model="newForm.lugar" placeholder="Destino del viaje" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-600">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700">Centro de costo</label>
                                <input type="text" x-model="newForm.centro_costo" readonly placeholder="Se asigna automáticamente" class="mt-1 block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500 shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div x-show="newStep === 2">
                        <p class="text-sm text-slate-500 mb-4">Selecciona el personal que participa en la comisión.</p>
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-slate-700">Agregar persona</label>
                            <select class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600"
                                    x-on:change="if($event.target.value) { newForm.personal.push({ id: Date.now(), name: $event.target.options[$event.target.selectedIndex].text, dias: calcDiasPeriodo(), categoria: 'Técnico', anticipo: 0 }); $event.target.value = ''; }">
                                <option value="">Buscar persona...</option>
                                @foreach($personalDisponible ?? [] as $per)
                                    <option value="{{ $per->id }}">{{ $per->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-center gap-2 mb-4">
                            <span class="text-xs font-medium text-slate-500">Total:</span>
                            <span class="inline-flex items-center rounded-full bg-gpt-100 px-2.5 py-0.5 text-xs font-semibold text-gpt-700" x-text="totalPersonas + ' persona(s)'"></span>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Persona</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Días</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Categoría</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500">Anticipo</th>
                                        <th class="px-3 py-2"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <template x-for="(p, idx) in newForm.personal" :key="p.id">
                                        <tr class="text-sm">
                                            <td class="px-3 py-2">
                                                <span x-text="p.name" class="font-medium text-slate-900"></span>
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" x-model="p.dias" min="1" class="w-16 rounded-md border border-slate-200 px-2 py-1 text-sm focus:border-gpt-600 focus:ring-gpt-600">
                                            </td>
                                            <td class="px-3 py-2">
                                                <select x-model="p.categoria" class="rounded-md border border-slate-200 px-2 py-1 text-sm focus:border-gpt-600 focus:ring-gpt-600">
                                                    <option value="Técnico">Técnico</option>
                                                    <option value="Supervisor">Supervisor</option>
                                                    <option value="Administrativo">Administrativo</option>
                                                </select>
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                <input type="number" x-model="p.anticipo" min="0" class="w-20 rounded-md border border-slate-200 px-2 py-1 text-sm text-right focus:border-gpt-600 focus:ring-gpt-600">
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <button @@click="newForm.personal.splice(idx, 1)" class="rounded p-1 text-slate-400 hover:text-gpt-red-600 hover:bg-red-50">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                    <tr x-show="newForm.personal.length === 0">
                                        <td colspan="5" class="px-3 py-8 text-center text-sm text-slate-400">Sin personal agregado</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div x-show="newStep === 3">
                        <p class="text-sm text-slate-500 mb-4">Conceptos de gasto estimados para esta comisión.</p>
                        <div class="overflow-x-auto rounded-lg border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Concepto</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Días</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold uppercase text-slate-500">Monto estimado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    <template x-for="(partida, idx) in newForm.partidas" :key="idx">
                                        <tr class="text-sm">
                                            <td class="px-3 py-2">
                                                <span x-text="partida.concepto" class="font-medium text-slate-900"></span>
                                            </td>
                                            <td class="px-3 py-2">
                                                <input type="number" x-model="partida.dias" min="0" class="w-16 rounded-md border border-slate-200 px-2 py-1 text-sm focus:border-gpt-600 focus:ring-gpt-600"
                                                       x-on:change="partida.monto_estimado = (parseInt(partida.dias) || 0) * (parseFloat(partida.tasa) || 0)">
                                            </td>
                                            <td class="px-3 py-2 text-right">
                                                <input type="number" x-model="partida.monto_estimado" min="0" class="w-28 rounded-md border border-slate-200 px-2 py-1 text-sm text-right focus:border-gpt-600 focus:ring-gpt-600">
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4 flex items-center justify-end gap-2 rounded-md bg-slate-50 px-4 py-3">
                            <span class="text-sm font-medium text-slate-700">Total estimado:</span>
                            <span class="text-lg font-semibold text-slate-900" x-text="'$ ' + totalMonto.toLocaleString('es-MX') + ' MXN'"></span>
                        </div>
                    </div>

                    <div x-show="newStep === 4">
                        <p class="text-sm text-slate-500 mb-4">Adjunta documentos de soporte (boletos, reservaciones, cotizaciones).</p>
                        <div class="rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                            <svg class="mx-auto h-10 w-10 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                            <p class="mt-3 text-sm font-medium text-slate-700">Arrastra archivos aquí o haz clic para seleccionar</p>
                            <p class="mt-1 text-xs text-slate-400">PDF, imágenes, documentos (máx. 10 MB)</p>
                            <input type="file" multiple class="mt-4 text-sm text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-gpt-600 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-white hover:file:bg-gpt-700">
                        </div>
                        <div class="mt-4 space-y-2">
                            <template x-for="(file, idx) in newForm.adjuntos" :key="idx">
                                <div class="flex items-center justify-between rounded-lg border border-slate-200 px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                        <span x-text="file.name" class="text-sm text-slate-700"></span>
                                    </div>
                                    <button @@click="newForm.adjuntos.splice(idx, 1)" class="rounded p-1 text-slate-400 hover:text-gpt-red-600 hover:bg-red-50">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4">
                    <div>
                        <button x-show="newStep > 1" @@click="prevStep()" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                            Anterior
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @@click="closeNewModal()" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Cancelar</button>
                        <button x-show="newStep < 4" @@click="nextStep()" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-700 transition-colors">
                            Siguiente
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                        </button>
                        <button x-show="newStep === 4" @@click="submitSolicitud()" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-700 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.125A59.769 59.769 0 0121.485 12 59.768 59.768 0 013.27 20.875L5.999 12zm0 0h7.5"/></svg>
                            Enviar a aprobación
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="detail-drawer-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/50" @@click="closeDetail()" x-transition.opacity></div>

        <div class="fixed inset-y-0 right-0 z-50 flex w-full max-w-[560px] flex-col bg-white shadow-xl" x-show="detailOpen" x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-medium text-slate-900" id="detail-drawer-title">Detalle de solicitud</h2>
                </div>
                <button @@click="closeDetail()" class="rounded-lg p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <span class="text-sm font-semibold text-slate-900">VTC-2025-018</span>
                        @php $detEstado = $solicitud->estado ?? 'en_aprobacion_n1'; @endphp
                        <span class="ml-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ match($detEstado) { 'aprobada' => 'bg-green-100 text-green-800', 'rechazada' => 'bg-gpt-red-100 text-gpt-red-800', default => 'bg-blue-100 text-blue-800' } }}">
                            {{ match($detEstado) { 'borrador' => 'Borrador', 'en_aprobacion_n1' => 'En aprobación N1', 'en_aprobacion_n2' => 'En aprobación N2', 'aprobada' => 'Aprobada', 'rechazada' => 'Rechazada', default => $detEstado } }}
                        </span>
                    </div>
                    @if(($solicitud->estado ?? '') === 'aprobada')
                        <button class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            Generar PDF firmable
                        </button>
                    @endif
                </div>

                <dl class="space-y-4 mb-6">
                    <div>
                        <dt class="text-xs font-medium uppercase text-slate-500">Proyecto</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $solicitud->proyecto->nombre ?? '—' }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-medium uppercase text-slate-500">Fecha inicio</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $solicitud->fecha_inicio ? \Carbon\Carbon::parse($solicitud->fecha_inicio)->format('d/m/Y') : '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase text-slate-500">Fecha fin</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $solicitud->fecha_fin ? \Carbon\Carbon::parse($solicitud->fecha_fin)->format('d/m/Y') : '—' }}</dd>
                        </div>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-slate-500">Justificación</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $solicitud->justificacion ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-slate-500">Lugar</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $solicitud->lugar ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium uppercase text-slate-500">Total</dt>
                        <dd class="mt-1 text-lg font-semibold text-slate-900">$ {{ number_format($solicitud->total ?? 0, 0) }} MXN</dd>
                    </div>
                </dl>

                <div class="mb-6">
                    <h3 class="text-sm font-semibold uppercase text-slate-500 mb-3">Flujo de aprobación</h3>
                    <div class="flex items-center gap-0">
                        @php
                            $flujoEstados = ['solicitante', 'servs_generales', 'direccion', 'aprobada'];
                            $flujoLabels = ['Solicitante', 'Servs Generales', 'Dirección', 'Aprobado'];
                            $currentEstado = $solicitud->estado ?? 'en_aprobacion_n1';
                            $pasoSolicitante = true;
                            $pasoSg = in_array($currentEstado, ['en_aprobacion_n1', 'en_aprobacion_n2', 'aprobada', 'rechazada']);
                            $pasoDir = in_array($currentEstado, ['en_aprobacion_n2', 'aprobada', 'rechazada']);
                            $pasoAprobado = $currentEstado === 'aprobada';
                            $flujoCompletados = [$pasoSolicitante, $pasoSg, $pasoDir, $pasoAprobado];
                        @endphp
                        @foreach($flujoLabels as $i => $label)
                            <div class="flex items-center">
                                <div class="flex flex-col items-center">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-semibold {{ $flujoCompletados[$i] ? 'bg-gpt-600 text-white' : 'bg-slate-200 text-slate-500' }}">
                                        @if($flujoCompletados[$i])
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            {{ $i + 1 }}
                                        @endif
                                    </div>
                                    <span class="mt-1 text-[10px] font-medium {{ $flujoCompletados[$i] ? 'text-gpt-600' : 'text-slate-400' }}">{{ $label }}</span>
                                </div>
                                @if($i < count($flujoLabels) - 1)
                                    <div class="h-0.5 w-12 {{ $flujoCompletados[$i] ? 'bg-gpt-600' : 'bg-slate-200' }}"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                @if(($solicitud->estado ?? '') === 'rechazada')
                    <div class="mb-6 rounded-md bg-gpt-red-50 p-4">
                        <p class="text-sm font-medium text-gpt-red-700">Motivo del rechazo</p>
                        <p class="mt-1 text-sm text-gpt-red-600">{{ $solicitud->motivo_rechazo ?? 'No especificado' }}</p>
                        <button class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-gpt-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-red-700 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                            Editar y reenviar
                        </button>
                    </div>
                @endif

                @if(in_array($solicitud->estado ?? '', ['en_aprobacion_n1', 'en_aprobacion_n2']))
                    <div class="rounded-lg border border-slate-200 p-4">
                        <h3 class="text-sm font-semibold text-slate-900 mb-3">Acción de aprobación</h3>
                        <div class="space-y-3">
                            <div x-show="showRechazoTextarea">
                                <label class="block text-sm font-medium text-slate-700">Razón de rechazo</label>
                                <textarea x-model="rechazoMotivo" rows="3" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-red-600 focus:ring-gpt-red-600" placeholder="Describe el motivo del rechazo..."></textarea>
                            </div>
                            <div class="flex items-center gap-2">
                                <button class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Aprobar
                                </button>
                                <button @@click="showRechazoTextarea = !showRechazoTextarea" class="inline-flex items-center gap-1.5 rounded-lg border border-gpt-red-300 bg-white px-4 py-2 text-sm font-medium text-gpt-red-600 hover:bg-gpt-red-50 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Rechazar
                                </button>
                            </div>
                            <button x-show="showRechazoTextarea" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-red-700 transition-colors">
                                Confirmar rechazo
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</x-layouts.app>
