<div>
    @section('title', 'Nueva oportunidad')

    <div class="mb-6">
        <h2 class="text-2xl font-medium text-slate-900">Nueva oportunidad</h2>
        <p class="mt-1 text-sm text-slate-500">Crea una nueva oportunidad comercial y asigna CP</p>
    </div>

    {{-- Stepper --}}
    <div class="mb-8">
        <div class="flex items-center justify-center">
            @foreach(['Datos básicos', 'Resumen ejecutivo', 'Asignación'] as $index => $label)
                @php $stepNum = $index + 1; @endphp
                <div class="flex items-center">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-medium
                        {{ $step >= $stepNum ? 'bg-gpt-600 text-white' : 'bg-slate-200 text-slate-500' }}">
                        @if($step > $stepNum)
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        @else
                            {{ $stepNum }}
                        @endif
                    </div>
                    <span class="ml-2 text-sm font-medium {{ $step >= $stepNum ? 'text-slate-900' : 'text-slate-400' }}">{{ $label }}</span>
                </div>
                @if($stepNum < 3)
                    <div class="mx-4 h-0.5 w-12 {{ $step > $stepNum ? 'bg-gpt-600' : 'bg-slate-200' }}"></div>
                @endif
            @endforeach
        </div>
    </div>

    @if(session()->has('success'))
        <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <div class="rounded-lg border border-slate-200 bg-white p-6">
        {{-- Step 1: Datos básicos --}}
        @if($step === 1)
            <h3 class="mb-4 text-lg font-medium text-slate-900">Datos del cliente</h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Cliente</label>
                    <select wire:model="cliente_id" class="mt-1 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200">
                        <option value="">Seleccionar cliente</option>
                        @foreach($clientes as $c)
                            <option value="{{ $c->id }}">{{ $c->razon_social }} ({{ $c->alias }})</option>
                        @endforeach
                    </select>
                    @error('cliente_id') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Sublinea</label>
                    <select wire:model="sublinea_id" class="mt-1 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200">
                        <option value="">Seleccionar sublinea</option>
                        @foreach($sublineas as $s)
                            <option value="{{ $s->id }}">{{ $s->codigo }} — {{ $s->nombre }}</option>
                        @endforeach
                    </select>
                    @error('sublinea_id') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Lugar</label>
                    <select wire:model="lugar_id" class="mt-1 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200">
                        <option value="">Seleccionar lugar</option>
                        @foreach($lugares as $l)
                            <option value="{{ $l->id }}">{{ $l->nombre }} ({{ $l->tipo }})</option>
                        @endforeach
                    </select>
                    @error('lugar_id') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Usuario final</label>
                    <input type="text" wire:model="usuario_final" class="mt-1 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Sector</label>
                    <input type="text" wire:model="sector" class="mt-1 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Contacto</label>
                    <input type="text" wire:model="contacto" placeholder="Nombre del contacto" class="mt-1 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200">
                    @error('contacto') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700">Datos de contacto</label>
                    <textarea wire:model="datos_contacto" rows="2" placeholder="Email, teléfono, cargo..." class="mt-1 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200"></textarea>
                    @error('datos_contacto') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

        {{-- Step 2: Resumen ejecutivo --}}
        @elseif($step === 2)
            <h3 class="mb-4 text-lg font-medium text-slate-900">Resumen ejecutivo</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Alcance del proyecto <span class="text-gpt-red-600">*</span></label>
                    <textarea wire:model="alcance" rows="5" class="mt-1 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200" placeholder="Describe el alcance del proyecto..."></textarea>
                    @error('alcance') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Monto estimado (USD)</label>
                        <input type="number" wire:model="monto_usd" min="0" step="0.01" placeholder="0.00" class="mt-1 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200">
                        @error('monto_usd') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Ponderación</label>
                        <select wire:model="ponderacion_id" class="mt-1 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200">
                            @foreach($ponderacionesCatalogo as $p)
                                <option value="{{ $p->id }}">{{ $p->porcentaje }}% — {{ $p->concepto }}</option>
                            @endforeach
                        </select>
                        @error('ponderacion_id') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Plazo estimado</label>
                        <input type="text" wire:model="plazo_estimado" class="mt-1 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200" placeholder="Ej: 3 meses">
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Fecha inicio planeada</label>
                        <input type="date" wire:model="fecha_inicio_planeada" class="mt-1 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Fecha fin planeada</label>
                        <input type="date" wire:model="fecha_fin_planeada" class="mt-1 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200">
                    </div>
                </div>
            </div>

        {{-- Step 3: Asignación --}}
        @else
            <h3 class="mb-4 text-lg font-medium text-slate-900">Asignación</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Director DN responsable</label>
                    <select wire:model="director_dn_id" class="mt-1 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200">
                        <option value="">Seleccionar director</option>
                        @foreach($directores as $d)
                            <option value="{{ $d->id }}">{{ $d->name }} ({{ $d->puesto }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Notas adicionales</label>
                    <textarea wire:model="notas" rows="3" class="mt-1 block w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200"></textarea>
                </div>
            </div>
        @endif

        {{-- Navigation --}}
        <div class="mt-8 flex justify-between">
            @if($step > 1)
                <x-button variant="secondary" wire:click="prevStep">Anterior</x-button>
            @else
                <div></div>
            @endif

            @if($step < 3)
                <x-button variant="primary" wire:click="nextStep">Siguiente</x-button>
            @else
                <x-button variant="primary" wire:click="save">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Crear oportunidad y asignar CP
                </x-button>
            @endif
        </div>
    </div>
</div>
