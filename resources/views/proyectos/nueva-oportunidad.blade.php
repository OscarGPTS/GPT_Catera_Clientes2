<x-layouts.app>
    <x-slot name="header">
        <div>
            <nav class="flex text-sm text-slate-400 mb-1" aria-label="Breadcrumb">
                <a href="{{ route('proyectos.oportunidades') }}" class="hover:text-gpt-600 transition-colors">Oportunidades</a>
                <span class="mx-2">/</span>
                <span class="text-slate-600">Nueva oportunidad</span>
            </nav>
            <h2 class="text-2xl font-medium text-slate-900">Nueva oportunidad</h2>
            <p class="mt-1 text-sm text-slate-500">Complete la información del nuevo proyecto</p>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('proyectos.store-oportunidad') }}">
        @csrf

        <div class="space-y-6">
            {{-- Sección 1: Datos del cliente --}}
            <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h3 class="text-base font-medium text-slate-900">1. Datos del cliente</h3>
                    <p class="mt-0.5 text-sm text-slate-500">Información del cliente y línea de negocio</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="cliente_id" class="block text-sm font-medium text-slate-700">Cliente <span class="text-gpt-red-600">*</span></label>
                            <select id="cliente_id" name="cliente_id" required class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <option value="">Seleccionar cliente</option>
                                @foreach($clientes ?? [] as $cliente)
                                    <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>{{ $cliente->nombre }}</option>
                                @endforeach
                            </select>
                            @error('cliente_id') <p class="mt-1 text-sm text-gpt-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="sublinea_id" class="block text-sm font-medium text-slate-700">Sublinea <span class="text-gpt-red-600">*</span></label>
                            <select id="sublinea_id" name="sublinea_id" required class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <option value="">Seleccionar sublinea</option>
                                @foreach($sublineas ?? [] as $sublinea)
                                    <option value="{{ $sublinea->id }}" @selected(old('sublinea_id') == $sublinea->id)>{{ $sublinea->nombre }}</option>
                                @endforeach
                            </select>
                            @error('sublinea_id') <p class="mt-1 text-sm text-gpt-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="usuario_final" class="block text-sm font-medium text-slate-700">Usuario final</label>
                            <input type="text" id="usuario_final" name="usuario_final" value="{{ old('usuario_final') }}" placeholder="Ej. Secretaría de la Defensa Nacional" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-600">
                            @error('usuario_final') <p class="mt-1 text-sm text-gpt-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="sector" class="block text-sm font-medium text-slate-700">Sector</label>
                            <select id="sector" name="sector" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <option value="">Seleccionar sector</option>
                                <option value="gobierno" @selected(old('sector') == 'gobierno')>Gobierno</option>
                                <option value="defensa" @selected(old('sector') == 'defensa')>Defensa</option>
                                <option value="privado" @selected(old('sector') == 'privado')>Privado</option>
                                <option value="educacion" @selected(old('sector') == 'educacion')>Educación</option>
                                <option value="salud" @selected(old('sector') == 'salud')>Salud</option>
                                <option value="energia" @selected(old('sector') == 'energia')>Energía</option>
                            </select>
                            @error('sector') <p class="mt-1 text-sm text-gpt-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sección 2: Resumen ejecutivo --}}
            <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h3 class="text-base font-medium text-slate-900">2. Resumen ejecutivo</h3>
                    <p class="mt-0.5 text-sm text-slate-500">Alcance del proyecto y plazo estimado</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label for="alcance" class="block text-sm font-medium text-slate-700">Alcance <span class="text-gpt-red-600">*</span></label>
                            <textarea id="alcance" name="alcance" rows="5" required class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-600" placeholder="Describa el alcance del proyecto, objetivos y entregables principales...">{{ old('alcance') }}</textarea>
                            @error('alcance') <p class="mt-1 text-sm text-gpt-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="plazo_estimado" class="block text-sm font-medium text-slate-700">Plazo estimado</label>
                            <input type="text" id="plazo_estimado" name="plazo_estimado" value="{{ old('plazo_estimado') }}" placeholder="Ej. 6 meses, Q2 2026" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-600">
                            @error('plazo_estimado') <p class="mt-1 text-sm text-gpt-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sección 3: Asignación --}}
            <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4">
                    <h3 class="text-base font-medium text-slate-900">3. Asignación</h3>
                    <p class="mt-0.5 text-sm text-slate-500">Designar responsable del proyecto</p>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label for="director_dn" class="block text-sm font-medium text-slate-700">Director / Líder <span class="text-gpt-red-600">*</span></label>
                            <select id="director_dn" name="director_dn" required class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                <option value="">Seleccionar líder</option>
                                @foreach($directores ?? [] as $director)
                                    <option value="{{ $director->id }}" @selected(old('director_dn') == $director->id)>{{ $director->name }}</option>
                                @endforeach
                            </select>
                            @error('director_dn') <p class="mt-1 text-sm text-gpt-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
            <a href="{{ route('proyectos.oportunidades') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-5 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Crear oportunidad
            </button>
        </div>
    </form>
</x-layouts.app>
