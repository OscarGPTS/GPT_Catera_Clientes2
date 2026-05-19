<div>
    @section('title', 'Viáticos')

    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl sm:text-2xl font-medium text-slate-900">Viáticos</h2>
                <p class="mt-1 text-sm text-slate-500">FO-GPT-SSGG-01-A &middot; Gastos de viaje y viáticos</p>
            </div>
            <button type="button" wire:click="openNewModal" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors min-h-[48px]">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Nueva solicitud
            </button>
        </div>

        @if($successMessage)
            <div wire:transition.opacity.duration.500ms class="rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800">{{ $successMessage }}</div>
        @endif

        @if(session('success'))
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="border-b border-slate-200">
            <nav class="-mb-px flex space-x-6 overflow-x-auto">
                <button wire:click="$set('tab', 'pendientes')" class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors {{ $tab === 'pendientes' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                    Pendientes
                    <span class="ml-2 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">{{ $this->pendientesCount }}</span>
                </button>
                <button wire:click="$set('tab', 'aprobadas')" class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors {{ $tab === 'aprobadas' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                    Aprobadas
                </button>
                <button wire:click="$set('tab', 'rechazadas')" class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors {{ $tab === 'rechazadas' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                    Rechazadas
                </button>
                <button wire:click="$set('tab', 'todas')" class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors {{ $tab === 'todas' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300' }}">
                    Todas
                </button>
            </nav>
        </div>

        <div class="space-y-4">
            @forelse($this->solicitudes as $solicitud)
                @php
                    $estadoBadge = match($solicitud->status) {
                        'borrador' => ['label' => 'Borrador', 'class' => 'bg-slate-100 text-slate-700'],
                        'pendiente_serv_grales' => ['label' => 'Pendiente SG', 'class' => 'bg-blue-100 text-blue-800'],
                        'pendiente_direccion' => ['label' => 'Pendiente Dir.', 'class' => 'bg-blue-100 text-blue-800'],
                        'aprobado' => ['label' => 'Aprobada', 'class' => 'bg-green-100 text-green-800'],
                        'rechazado' => ['label' => 'Rechazada', 'class' => 'bg-red-100 text-red-800'],
                        default => ['label' => $solicitud->status ?? '—', 'class' => 'bg-slate-100 text-slate-700'],
                    };
                @endphp
                <div class="rounded-lg border border-slate-200 bg-white p-5 hover:border-slate-300 hover:shadow-sm transition-all cursor-pointer" wire:click="openDetail({{ $solicitud->id }})">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-semibold text-slate-900">{{ $solicitud->proyecto->cp_numero ?? '—' }}</span>
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estadoBadge['class'] }}">
                                {{ $estadoBadge['label'] }}
                            </span>
                        </div>
                        <span class="text-sm font-semibold text-slate-900">$ {{ number_format($solicitud->monto_total ?? 0, 0) }} MXN</span>
                    </div>

                    <div class="mt-3 flex items-center gap-6 text-sm text-slate-600">
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                            {{ $solicitud->fecha_inicio ? \Carbon\Carbon::parse($solicitud->fecha_inicio)->format('d-m') : '—' }}
                            &rarr;
                            {{ $solicitud->fecha_fin ? \Carbon\Carbon::parse($solicitud->fecha_fin)->format('d-m') : '—' }}
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0"/></svg>
                            {{ $solicitud->solicitante->name ?? '—' }}
                        </span>
                    </div>

                    @if($solicitud->destino)
                        <p class="mt-2 text-sm text-slate-600">
                            <span class="font-medium">Destino:</span> {{ $solicitud->destino }}
                        </p>
                    @endif

                    @if($solicitud->motivo)
                        <p class="mt-1 text-sm text-slate-600 truncate">{{ \Illuminate\Support\Str::limit($solicitud->motivo, 100) }}</p>
                    @endif

                    <div class="mt-4 flex items-center gap-1">
                        @php
                            $pasoSg = in_array($solicitud->status, ['pendiente_direccion', 'aprobado', 'rechazado']);
                            $pasoDir = in_array($solicitud->status, ['aprobado', 'rechazado']);
                            $pasoAprobado = $solicitud->status === 'aprobado';
                            $flujoCompletados = [true, $pasoSg, $pasoDir, $pasoAprobado];
                            $flujoLabels = ['Solicitante', 'Servs Generales', 'Dirección', 'Aprobado'];
                        @endphp
                        @foreach($flujoLabels as $i => $label)
                            <div class="flex items-center gap-1">
                                <div class="flex items-center justify-center h-5 w-5 rounded-full text-[10px] font-semibold {{ $flujoCompletados[$i] ? 'bg-gpt-600 text-white' : 'bg-slate-200 text-slate-500' }}">
                                    @if($flujoCompletados[$i])
                                        <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </div>
                                @if($i < count($flujoLabels) - 1)
                                    <div class="h-0.5 w-6 {{ $flujoCompletados[$i] ? 'bg-gpt-600' : 'bg-slate-200' }}"></div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    @if($solicitud->status === 'rechazado')
                        <div class="mt-3 rounded-md bg-red-50 px-3 py-2">
                            <p class="text-sm font-medium text-red-700">Motivo: {{ $solicitud->motivo_rechazo ?? 'No especificado' }}</p>
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-lg border border-slate-200 bg-white p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="mt-4 text-lg font-medium text-slate-900">Sin viáticos registrados</h3>
                    <p class="mt-1 text-sm text-slate-500">No se encontraron solicitudes de viáticos.</p>
                </div>
            @endforelse

            <div class="mt-4">
                {{ $this->solicitudes->links() }}
            </div>
        </div>
    </div>

    {{-- NEW SOLICITUD MODAL --}}
    @if($newModalOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="fixed inset-0 bg-slate-900/50" wire:click="closeNewModal"></div>

                <div class="relative z-50 w-full max-w-3xl rounded-xl border border-slate-200 bg-white shadow-xl">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                        <div>
                            <h2 class="text-lg font-medium text-slate-900" id="modal-title">Nueva solicitud de viáticos</h2>
                            <p class="text-xs text-slate-500">Sección {{ $newStep }} de 4</p>
                        </div>
                        <button wire:click="closeNewModal" class="rounded-lg p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="flex border-b border-slate-200">
                        @foreach([1 => 'Datos generales', 2 => 'Personal', 3 => 'Partidas', 4 => 'Adjuntos'] as $step => $label)
                            <button wire:click="$set('newStep', {{ $step }})" class="flex-1 border-b-2 px-4 py-3 text-center text-sm font-medium transition-colors {{ $newStep >= $step ? 'text-gpt-600 border-gpt-600' : 'text-slate-400 border-transparent' }}">
                                <span class="mr-1.5 inline-flex h-5 w-5 items-center justify-center rounded-full text-xs font-semibold {{ $newStep >= $step ? 'bg-gpt-600 text-white' : 'bg-slate-200 text-slate-500' }}">{{ $step }}</span>
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>

                    <div class="max-h-[60vh] overflow-y-auto p-6">
                        @if($newStep === 1)
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Proyecto</label>
                                    <select wire:model="proyectoId" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                        <option value="">Seleccionar proyecto...</option>
                                        @foreach($proyectos as $p)
                                            <option value="{{ $p->id }}">{{ $p->cp_numero ?? '' }} - {{ $p->cliente->razon_social ?? '' }}</option>
                                        @endforeach
                                    </select>
                                    @error('proyectoId') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Fecha inicio</label>
                                        <input type="date" wire:model="fechaInicio" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                        @error('fechaInicio') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Fecha fin</label>
                                        <input type="date" wire:model="fechaFin" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                        @error('fechaFin') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                @if($this->calcDiasPeriodo() > 0)
                                    <div class="rounded-md bg-gpt-50 px-3 py-2">
                                        <p class="text-sm text-gpt-700">Período: <span class="font-semibold">{{ $this->calcDiasPeriodo() }}</span> días</p>
                                    </div>
                                @endif
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Justificación</label>
                                    <textarea wire:model="justificacion" rows="3" placeholder="Describa el motivo del viaje..." class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-600"></textarea>
                                    @error('justificacion') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700">Lugar / Destino</label>
                                    <input type="text" wire:model="lugar" placeholder="Destino del viaje" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-600">
                                    @error('lugar') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        @endif

                        @if($newStep === 2)
                            <p class="text-sm text-slate-500 mb-4">Selecciona el personal que participa en la comisión.</p>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-slate-700">Agregar persona</label>
                                <select class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600" onchange="if(this.value) { Livewire.dispatch('add-person', {userId: this.value, userName: this.options[this.selectedIndex].text}); this.value=''; }">
                                    <option value="">Buscar persona...</option>
                                    @foreach($personalDisponible as $per)
                                        <option value="{{ $per->id }}">{{ $per->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="text-xs font-medium text-slate-500">Total:</span>
                                <span class="inline-flex items-center rounded-full bg-gpt-100 px-2.5 py-0.5 text-xs font-semibold text-gpt-700">{{ count($personalSeleccionado) }} persona(s)</span>
                            </div>
                            @if(count($personalSeleccionado) > 0)
                                <div class="overflow-x-auto rounded-lg border border-slate-200">
                                    <table class="min-w-full divide-y divide-slate-200">
                                        <thead class="bg-slate-50">
                                            <tr>
                                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Persona</th>
                                                <th class="px-3 py-2 text-left text-xs font-semibold uppercase text-slate-500">Días</th>
                                                <th class="px-3 py-2"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            @foreach($personalSeleccionado as $idx => $p)
                                                <tr class="text-sm">
                                                    <td class="px-3 py-2 font-medium text-slate-900">{{ $p['name'] }}</td>
                                                    <td class="px-3 py-2">
                                                        <input type="number" wire:model="personalSeleccionado.{{ $idx }}.dias" min="1" class="w-16 rounded-md border border-slate-200 px-2 py-1 text-sm focus:border-gpt-600 focus:ring-gpt-600">
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        <button wire:click="removePersonal({{ $idx }})" class="rounded p-1 text-slate-400 hover:text-red-600 hover:bg-red-50">
                                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-sm text-slate-400 text-center py-8">Sin personal agregado</p>
                            @endif
                        @endif

                        @if($newStep === 3)
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
                                        @foreach($partidas as $idx => $partida)
                                            <tr class="text-sm">
                                                <td class="px-3 py-2 font-medium text-slate-900">{{ $partida['concepto'] }}</td>
                                                <td class="px-3 py-2">
                                                    <input type="number" wire:model="partidas.{{ $idx }}.dias" min="0" class="w-16 rounded-md border border-slate-200 px-2 py-1 text-sm focus:border-gpt-600 focus:ring-gpt-600">
                                                </td>
                                                <td class="px-3 py-2 text-right">
                                                    <input type="number" wire:model="partidas.{{ $idx }}.monto_estimado" min="0" class="w-28 rounded-md border border-slate-200 px-2 py-1 text-sm text-right focus:border-gpt-600 focus:ring-gpt-600">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4 flex items-center justify-end gap-2 rounded-md bg-slate-50 px-4 py-3">
                                <span class="text-sm font-medium text-slate-700">Total estimado:</span>
                                <span class="text-lg font-semibold text-slate-900">$ {{ number_format($this->totalMonto, 0) }} MXN</span>
                            </div>
                        @endif

                        @if($newStep === 4)
                            <div class="rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 p-8 text-center">
                                <svg class="mx-auto h-10 w-10 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/></svg>
                                <p class="mt-3 text-sm font-medium text-slate-700">Adjunta documentos de soporte</p>
                                <p class="mt-1 text-xs text-slate-400">PDF, imágenes, documentos (máx. 10 MB)</p>
                                <p class="mt-2 text-xs text-slate-400 italic">La carga de archivos estará disponible próximamente.</p>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-200 px-6 py-4">
                        <div>
                            @if($newStep > 1)
                                <button type="button" wire:click="prevStep" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
                                    Anterior
                                </button>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" wire:click="closeNewModal" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 transition-colors">Cancelar</button>
                            @if($newStep < 4)
                                <button type="button" wire:click="nextStep" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-700 transition-colors">
                                    Siguiente
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                                </button>
                            @else
                                <button type="button" wire:click="submitSolicitud" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-700 transition-colors">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.125A59.769 59.769 0 0121.485 12 59.768 59.768 0 013.27 20.875L5.999 12zm0 0h7.5"/></svg>
                                    Enviar a aprobación
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- DETAIL DRAWER --}}
    @if($detailViaticoId && $this->detailViatico)
        @php
            $solicitud = $this->detailViatico;
            $detEstado = match($solicitud->status) {
                'borrador' => 'Borrador',
                'pendiente_serv_grales' => 'Pendiente Servs Generales',
                'pendiente_direccion' => 'Pendiente Dirección',
                'aprobado' => 'Aprobada',
                'rechazado' => 'Rechazada',
                default => $solicitud->status,
            };
            $estadoClass = match($solicitud->status) {
                'aprobado' => 'bg-green-100 text-green-800',
                'rechazado' => 'bg-red-100 text-red-800',
                default => 'bg-blue-100 text-blue-800',
            };
            $canApproveSg = auth()->user()->can('aprobar viaticos servicios generales');
            $canApproveDir = auth()->user()->can('aprobar viaticos direccion');
            $showApproveActions = in_array($solicitud->status, ['pendiente_serv_grales', 'pendiente_direccion']);
        @endphp
        <div class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="detail-drawer-title" role="dialog" aria-modal="true">
            <div class="absolute inset-0 bg-slate-900/50" wire:click="closeDetail"></div>

            <div class="fixed inset-y-0 right-0 z-50 flex w-full max-w-[560px] flex-col bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                    <div>
                        <h2 class="text-lg font-medium text-slate-900" id="detail-drawer-title">Detalle de solicitud</h2>
                    </div>
                    <button wire:click="closeDetail" class="rounded-lg p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <span class="text-sm font-semibold text-slate-900">{{ $solicitud->proyecto->cp_numero ?? '—' }}</span>
                            <span class="ml-2 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estadoClass }}">
                                {{ $detEstado }}
                            </span>
                        </div>
                    </div>

                    <dl class="space-y-4 mb-6">
                        <div>
                            <dt class="text-xs font-medium uppercase text-slate-500">Proyecto</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $solicitud->proyecto->cliente->razon_social ?? '—' }}</dd>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                            <dd class="mt-1 text-sm text-slate-900">{{ $solicitud->motivo ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase text-slate-500">Destino</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $solicitud->destino ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase text-slate-500">Total</dt>
                            <dd class="mt-1 text-lg font-semibold text-slate-900">$ {{ number_format($solicitud->monto_total ?? 0, 0) }} MXN</dd>
                        </div>
                    </dl>

                    <div class="mb-6">
                        <h3 class="text-sm font-semibold uppercase text-slate-500 mb-3">Flujo de aprobación</h3>
                        <div class="flex items-center gap-0">
                            @php
                                $currentStatus = $solicitud->status;
                                $flujoCompletados = [
                                    true,
                                    in_array($currentStatus, ['pendiente_direccion', 'aprobado', 'rechazado']),
                                    in_array($currentStatus, ['aprobado', 'rechazado']),
                                    $currentStatus === 'aprobado',
                                ];
                                $flujoLabels = ['Solicitante', 'Servs Generales', 'Dirección', 'Aprobado'];
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

                    @if($solicitud->status === 'rechazado')
                        <div class="mb-6 rounded-md bg-red-50 p-4">
                            <p class="text-sm font-medium text-red-700">Motivo del rechazo</p>
                            <p class="mt-1 text-sm text-red-600">{{ $solicitud->motivo_rechazo ?? 'No especificado' }}</p>
                        </div>
                    @endif

                    @if($showApproveActions)
                        <div class="rounded-lg border border-slate-200 p-4">
                            <h3 class="text-sm font-semibold text-slate-900 mb-3">Acción de aprobación</h3>
                            <div class="space-y-3">
                                @if($showRechazoTextarea)
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700">Razón de rechazo</label>
                                        <textarea wire:model="rechazoMotivo" rows="3" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-red-600 focus:ring-red-600" placeholder="Describe el motivo del rechazo..."></textarea>
                                        @error('rechazoMotivo') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                                    </div>
                                @endif
                                <div class="flex items-center gap-2">
                                    @if($solicitud->status === 'pendiente_serv_grales' && $canApproveSg)
                                        <button wire:click="aprobar({{ $solicitud->id }})" class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            Aprobar (Servs Generales)
                                        </button>
                                    @elseif($solicitud->status === 'pendiente_direccion' && $canApproveDir)
                                        <button wire:click="aprobar({{ $solicitud->id }})" class="inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            Aprobar (Dirección)
                                        </button>
                                    @endif
                                    @if(!$showRechazoTextarea)
                                        <button wire:click="$toggle('showRechazoTextarea')" class="inline-flex items-center gap-1.5 rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                            Rechazar
                                        </button>
                                    @else
                                        <button wire:click="rechazar({{ $solicitud->id }})" class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 transition-colors">
                                            Confirmar rechazo
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>