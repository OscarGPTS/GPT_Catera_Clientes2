<x-layouts.app>
    <x-slot name="header">
        <div>
            <nav class="flex items-center gap-2 text-sm text-slate-500 mb-2" aria-label="Breadcrumb">
                <a href="{{ route('oportunidades.index') }}" class="hover:text-slate-700 transition-colors">Oportunidades</a>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('oportunidades.show', $proyecto) }}" class="hover:text-slate-700 transition-colors">{{ $proyecto->cp_numero ?? 'CP-?' }}</a>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="font-medium text-slate-900">Adjudicar</span>
            </nav>
            <h2 class="text-2xl font-medium text-slate-900">Adjudicar {{ $proyecto->cp_numero ?? $proyecto->tech_reference ?? 'Oportunidad' }}</h2>
            <p class="mt-1 text-sm text-slate-500">Registrar adjudicación del proyecto y asignar equipo para ejecución</p>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main form --}}
        <div class="lg:col-span-2">
            @if($proyecto->estado === 'presentado' || $proyecto->estado === 'adjudicado_pendiente')
                <form method="POST" action="{{ route('proyectos.adjudicar', $proyecto) }}" class="space-y-6">
                    @csrf
                    <div class="rounded-lg border border-slate-200 bg-white p-6 space-y-6">
                        <h3 class="text-lg font-medium text-slate-900">Datos de adjudicación</h3>

                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label for="fecha_inicio_planeada" class="block text-sm font-medium text-slate-700 mb-1">Fecha inicio planeada *</label>
                                <input type="date" name="fecha_inicio_planeada" id="fecha_inicio_planeada" required
                                       value="{{ old('fecha_inicio_planeada', $proyecto->fecha_inicio_planeada?->format('Y-m-d')) }}"
                                       class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                            </div>
                            <div>
                                <label for="fecha_fin_planeada" class="block text-sm font-medium text-slate-700 mb-1">Fecha fin planeada *</label>
                                <input type="date" name="fecha_fin_planeada" id="fecha_fin_planeada" required
                                       value="{{ old('fecha_fin_planeada', $proyecto->fecha_fin_planeada?->format('Y-m-d')) }}"
                                       class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                            </div>
                        </div>

                        <div>
                            <label for="metodo_distribucion_plurianual" class="block text-sm font-medium text-slate-700 mb-1">Método distribución plurianual *</label>
                            <select name="metodo_distribucion_plurianual" id="metodo_distribucion_plurianual" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <option value="dias_naturales" {{ old('metodo_distribucion_plurianual', $proyecto->metodo_distribucion_plurianual) === 'dias_naturales' ? 'selected' : '' }}>Días naturales</option>
                                <option value="hitos" {{ old('metodo_distribucion_plurianual', $proyecto->metodo_distribucion_plurianual) === 'hitos' ? 'selected' : '' }}>Hitos</option>
                            </select>
                        </div>

                        <div>
                            <label for="gerente_proyectos_id" class="block text-sm font-medium text-slate-700 mb-1">Gerente de Proyectos *</label>
                            <select name="gerente_proyectos_id" id="gerente_proyectos_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <option value="">Seleccionar...</option>
                                @foreach(\App\Models\User::role('gerente_proyectos')->where('status', 'active')->orderBy('name')->get() as $gp)
                                    <option value="{{ $gp->id }}" {{ (string)$proyecto->gerente_proyectos_id === (string)$gp->id ? 'selected' : '' }}>{{ $gp->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="gerente_operaciones_id" class="block text-sm font-medium text-slate-700 mb-1">Gerente de Operaciones</label>
                            <select name="gerente_operaciones_id" id="gerente_operaciones_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <option value="">Sin asignar</option>
                                @foreach(\App\Models\User::role('gerente_operaciones')->where('status', 'active')->orderBy('name')->get() as $go)
                                    <option value="{{ $go->id }}" {{ (string)$proyecto->gerente_operaciones_id === (string)$go->id ? 'selected' : '' }}>{{ $go->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="notas" class="block text-sm font-medium text-slate-700 mb-1">Notas de adjudicación</label>
                            <textarea name="notas" id="notas" rows="3" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-600" placeholder="Detalles de la adjudicación...">{{ old('notas') }}</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('oportunidades.show', $proyecto) }}" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</a>
                        <button type="submit" class="rounded-lg bg-gpt-600 px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                            <svg class="h-4 w-4 inline mr-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            Adjudicar proyecto
                        </button>
                    </div>
                </form>
            @elseif($proyecto->estado === 'adjudicado_firmado')
                <div class="rounded-lg border border-green-200 bg-green-50 p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <h3 class="mt-4 text-lg font-medium text-green-900">Proyecto adjudicado y firmado</h3>
                    <p class="mt-2 text-sm text-green-700">Este proyecto ya fue adjudicado y firmado. Se encuentra en estado de ejecución.</p>
                    <a href="{{ route('oportunidades.show', $proyecto) }}" class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                        Ver proyecto
                    </a>
                </div>
            @else
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    <h3 class="mt-4 text-lg font-medium text-amber-900">Estado no adjudicable</h3>
                    <p class="mt-2 text-sm text-amber-700">El estado actual del proyecto es <strong>{{ ucfirst(str_replace('_', ' ', $proyecto->estado)) }}</strong>. Solo se pueden adjudicar proyectos en estado "Presentado" o "Adjudicado pendiente".</p>
                    <a href="{{ route('oportunidades.show', $proyecto) }}" class="mt-4 inline-flex items-center gap-1.5 rounded-lg border border-amber-300 bg-white px-4 py-2 text-sm font-medium text-amber-700 hover:bg-amber-50 transition-colors">
                        Volver al proyecto
                    </a>
                </div>
            @endif
        </div>

        {{-- Sidebar: resumen --}}
        <div class="space-y-6">
            <div class="rounded-lg border border-slate-200 bg-white p-5">
                <h3 class="text-sm font-semibold text-slate-900 mb-3">Resumen del proyecto</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-500">CP</dt>
                        <dd class="font-mono text-slate-900">{{ $proyecto->cp_numero ?? 'Sin asignar' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-500">Tech Reference</dt>
                        <dd class="text-slate-900">{{ $proyecto->tech_reference ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-500">Cliente</dt>
                        <dd class="text-slate-900">{{ $proyecto->cliente->razon_social ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between text-sm">
                        <dt class="text-slate-500">Estado</dt>
                        <dd><x-badge :status="$proyecto->estado" /></dd>
                    </div>
                    @if($proyecto->cotizaciones->count() > 0)
                        <div class="border-t border-slate-100 pt-3">
                            <p class="text-xs font-medium text-slate-500 mb-2">COTIZACIONES</p>
                            @foreach($proyecto->cotizaciones as $cot)
                                <div class="flex justify-between text-sm py-1">
                                    <span class="text-slate-600">v{{ $cot->version }}</span>
                                    <span class="font-medium text-slate-900">$ {{ number_format($cot->precio_venta_final, 0, '.', ',') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</x-layouts.app>