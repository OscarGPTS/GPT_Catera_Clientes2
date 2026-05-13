<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-medium text-slate-900">Importar Oportunidades</h2>
                <p class="mt-1 text-sm text-slate-500">Carga masiva desde Excel / CSV con el formato estándar GPT Services</p>
            </div>
            <a href="{{ route('oportunidades.index') }}"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Volver
            </a>
        </div>
    </x-slot>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- PASO 1 — Subir archivo                                                --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if($paso === 'subir')
        <div class="mx-auto max-w-2xl">

            {{-- Guía de columnas esperadas --}}
            <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4">
                <p class="mb-2 text-sm font-semibold text-blue-800">Columnas esperadas en el archivo (en este orden):</p>
                <div class="flex flex-wrap gap-1.5">
                    @foreach(['CP','CLIENTE','CONTACTO','DATOS DE CONTACTO','LUGAR','ALCANCE','OFERTA (tech ref)','FECHA ENVÍO','FECHA MODIFICACION OFERTA','OFERTAS EMITIDAS','HITOS DE PAGO','RESPONSABLE','STATUS','OFERTA (PDF)','CONCEPTO ADJUDICACIÓN','% DE ADJUDICACION','%','Cartera Esperada'] as $col)
                        <span class="rounded bg-blue-100 px-1.5 py-0.5 font-mono text-[11px] text-blue-700">{{ $col }}</span>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-blue-600">Las columnas CONCEPTO ADJUDICACIÓN, % y Cartera Esperada se ignoran en la importación.</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-8 shadow-sm">
                <form wire:submit="procesarArchivo" class="space-y-6">

                    {{-- Zona de carga --}}
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Archivo Excel o CSV</label>
                        <div x-data="{ dragging: false }"
                             @dragover.prevent="dragging = true"
                             @dragleave.prevent="dragging = false"
                             @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change'))"
                             :class="dragging ? 'border-gpt-500 bg-gpt-50' : 'border-slate-300 bg-slate-50 hover:border-slate-400'"
                             class="relative flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed px-6 py-10 text-center transition-colors">

                            <svg class="mx-auto mb-3 h-10 w-10 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/>
                            </svg>

                            @if($archivo)
                                <p class="text-sm font-medium text-gpt-700">{{ $archivo->getClientOriginalName() }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ number_format($archivo->getSize() / 1024, 1) }} KB</p>
                            @else
                                <p class="text-sm font-medium text-slate-700">Arrastra el archivo aquí</p>
                                <p class="mt-1 text-xs text-slate-500">o haz clic para seleccionarlo — .xlsx, .xls, .csv (máx. 10 MB)</p>
                            @endif

                            <input wire:model="archivo" x-ref="fileInput"
                                   type="file" accept=".xlsx,.xls,.csv"
                                   class="absolute inset-0 h-full w-full cursor-pointer opacity-0">
                        </div>

                        @error('archivo')
                            <p class="mt-1.5 flex items-center gap-1 text-sm text-red-600">
                                <svg class="h-4 w-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Modo de importación --}}
                    <div>
                        <p class="mb-3 text-sm font-medium text-slate-700">Modo de importación</p>
                        <div class="space-y-3">

                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 transition-colors hover:bg-slate-50
                                          {{ $modo === 'agregar' ? 'border-gpt-500 bg-gpt-50' : '' }}">
                                <input type="radio" wire:model.live="modo" value="agregar" class="mt-0.5 h-4 w-4 text-gpt-600 border-slate-300 focus:ring-gpt-600">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Agregar nuevos registros</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Inserta las filas del archivo sin borrar los registros existentes. Los duplicados se agregarán de todas formas.</p>
                                </div>
                            </label>

                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-4 transition-colors hover:bg-slate-50
                                          {{ $modo === 'reemplazar' ? 'border-red-400 bg-red-50' : '' }}">
                                <input type="radio" wire:model.live="modo" value="reemplazar" class="mt-0.5 h-4 w-4 text-red-600 border-slate-300 focus:ring-red-600">
                                <div>
                                    <p class="text-sm font-medium text-slate-900">Reemplazar todo el pipeline</p>
                                    <p class="text-xs text-slate-500 mt-0.5">
                                        <span class="font-semibold text-red-600">⚠ Eliminará</span> todos los proyectos en estado
                                        <em>presentado, en revisión, cotizando y cotizado</em> antes de importar.
                                        Los proyectos adjudicados y en ejecución <strong>no se tocan</strong>.
                                    </p>
                                </div>
                            </label>

                        </div>
                    </div>

                    {{-- Botón --}}
                    <div class="flex justify-end">
                        <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-gpt-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors disabled:opacity-50"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="procesarArchivo">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </span>
                            <span wire:loading wire:target="procesarArchivo">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            </span>
                            <span wire:loading.remove wire:target="procesarArchivo">Procesar archivo</span>
                            <span wire:loading wire:target="procesarArchivo">Analizando...</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- PASO 2 — Vista previa                                                 --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if($paso === 'preview')
        @php
            $validas      = collect($filas)->where('_valido', true);
            $conError     = collect($filas)->where('_valido', false);
            $conAdvert    = collect($filas)->filter(fn($f) => $f['_valido'] && !empty($f['_advertencias']));
        @endphp

        {{-- Resumen --}}
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-center">
                <p class="text-2xl font-bold text-green-700">{{ $validas->count() }}</p>
                <p class="text-sm text-green-600">Filas válidas a importar</p>
            </div>
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-center">
                <p class="text-2xl font-bold text-amber-700">{{ $conAdvert->count() }}</p>
                <p class="text-sm text-amber-600">Con advertencias (se importarán)</p>
            </div>
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-center">
                <p class="text-2xl font-bold text-red-700">{{ $conError->count() }}</p>
                <p class="text-sm text-red-600">Con errores (se omitirán)</p>
            </div>
        </div>

        @if($modo === 'reemplazar')
            <div class="mb-4 flex items-start gap-3 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">
                <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                <span><strong>Modo reemplazar:</strong> Se eliminarán todos los proyectos en estado <em>presentado, en revisión, cotizando y cotizado</em> antes de insertar los {{ $validas->count() }} registros del archivo.</span>
            </div>
        @endif

        {{-- Tabla de preview --}}
        <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 w-8">Fila</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">CP</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Cliente</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Contacto</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Lugar</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 max-w-[200px]">Tech Reference</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">F. Envío</th>
                            <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Monto USD</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Responsable</th>
                            <th class="px-3 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">% Adj.</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                            <th class="px-3 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Info</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($filas as $fila)
                            @php
                                $rowClass = $fila['_valido']
                                    ? (empty($fila['_advertencias']) ? '' : 'bg-amber-50')
                                    : 'bg-red-50';
                            @endphp
                            <tr class="{{ $rowClass }}">
                                <td class="px-3 py-2 text-xs text-slate-400 font-mono">{{ $fila['fila'] }}</td>

                                <td class="px-3 py-2 text-xs font-mono text-slate-700">
                                    {{ $fila['cp_numero'] ?? '—' }}
                                </td>

                                <td class="px-3 py-2 text-xs whitespace-nowrap">
                                    @if($fila['cliente_id'])
                                        <span class="font-medium text-slate-900">{{ $fila['cliente_alias'] }}</span>
                                    @else
                                        <span class="font-medium text-red-700">{{ $fila['cliente_alias'] ?: '—' }}</span>
                                        <span class="block text-[10px] text-red-500">No encontrado</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2 text-xs text-slate-600 max-w-[140px] truncate"
                                    title="{{ $fila['contacto'] }}">
                                    {{ $fila['contacto'] ?? '—' }}
                                </td>

                                <td class="px-3 py-2 text-xs whitespace-nowrap">
                                    @if($fila['lugar_id'])
                                        <span class="text-slate-700">{{ $fila['lugar_nombre'] }}</span>
                                    @elseif($fila['lugar_nombre'])
                                        <span class="text-amber-700">{{ $fila['lugar_nombre'] }}</span>
                                        <span class="block text-[10px] text-amber-500">Sin catálogo</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2 text-xs text-slate-600 max-w-[200px] truncate"
                                    title="{{ $fila['tech_reference'] }}">
                                    {{ $fila['tech_reference'] ?? '—' }}
                                </td>

                                <td class="px-3 py-2 text-xs whitespace-nowrap text-slate-600">
                                    {{ $fila['fecha_envio'] ?? '—' }}
                                </td>

                                <td class="px-3 py-2 text-xs text-right font-medium text-slate-900 whitespace-nowrap">
                                    @if($fila['monto_usd'])
                                        $&nbsp;{{ number_format($fila['monto_usd'], 0, '.', ',') }}
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2 text-xs whitespace-nowrap">
                                    @if($fila['elaboro_id'])
                                        <span class="text-slate-700">{{ $fila['responsable_nombre'] }}</span>
                                    @elseif($fila['responsable_nombre'])
                                        <span class="text-amber-700">{{ $fila['responsable_nombre'] }}</span>
                                        <span class="block text-[10px] text-amber-500">No encontrado</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>

                                <td class="px-3 py-2 text-xs text-right whitespace-nowrap">
                                    <span class="rounded-full px-2 py-0.5 text-[11px] font-medium
                                        {{ $fila['ponderacion'] >= 75 ? 'bg-green-100 text-green-800' :
                                           ($fila['ponderacion'] >= 50 ? 'bg-amber-100 text-amber-800' :
                                           ($fila['ponderacion'] >= 25 ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600')) }}">
                                        {{ $fila['ponderacion'] }}%
                                    </span>
                                </td>

                                <td class="px-3 py-2 text-xs whitespace-nowrap text-slate-600">
                                    {{ $fila['estado'] }}
                                </td>

                                {{-- Errores / Advertencias --}}
                                <td class="px-3 py-2 text-xs max-w-[200px]">
                                    @foreach($fila['_errores'] as $err)
                                        <div class="flex items-start gap-1 text-red-700">
                                            <svg class="mt-0.5 h-3 w-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                            <span>{{ $err }}</span>
                                        </div>
                                    @endforeach
                                    @foreach($fila['_advertencias'] as $adv)
                                        <div class="flex items-start gap-1 text-amber-700">
                                            <svg class="mt-0.5 h-3 w-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            <span>{{ $adv }}</span>
                                        </div>
                                    @endforeach
                                    @if(empty($fila['_errores']) && empty($fila['_advertencias']))
                                        <svg class="h-4 w-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Acciones --}}
        <div class="mt-6 flex items-center justify-between">
            <button wire:click="volver"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Cambiar archivo
            </button>

            @if($validas->count() > 0)
                <button wire:click="confirmarImportacion"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 rounded-lg {{ $modo === 'reemplazar' ? 'bg-red-600 hover:bg-red-700' : 'bg-gpt-600 hover:bg-gpt-700' }} px-6 py-2.5 text-sm font-medium text-white shadow-sm transition-colors disabled:opacity-50">
                    <span wire:loading.remove wire:target="confirmarImportacion">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    </span>
                    <span wire:loading wire:target="confirmarImportacion">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    </span>
                    <span wire:loading.remove wire:target="confirmarImportacion">
                        {{ $modo === 'reemplazar' ? 'Reemplazar e importar ' . $validas->count() . ' registros' : 'Importar ' . $validas->count() . ' registros' }}
                    </span>
                    <span wire:loading wire:target="confirmarImportacion">Importando...</span>
                </button>
            @else
                <p class="text-sm text-red-600 font-medium">No hay filas válidas para importar.</p>
            @endif
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- PASO 3 — Resultado                                                    --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @if($paso === 'resultado')
        <div class="mx-auto max-w-lg text-center">
            <div class="rounded-xl border border-green-200 bg-green-50 p-10 shadow-sm">
                <svg class="mx-auto mb-4 h-14 w-14 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-xl font-semibold text-green-800">¡Importación completada!</h3>
                <div class="mt-4 flex justify-center gap-8">
                    <div>
                        <p class="text-3xl font-bold text-green-700">{{ $insertados }}</p>
                        <p class="text-sm text-green-600">registros importados</p>
                    </div>
                    @if($omitidos > 0)
                        <div>
                            <p class="text-3xl font-bold text-amber-600">{{ $omitidos }}</p>
                            <p class="text-sm text-amber-600">omitidos (con errores)</p>
                        </div>
                    @endif
                </div>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                    <a href="{{ route('oportunidades.index') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-lg bg-gpt-600 px-6 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                        Ver oportunidades
                    </a>
                    <button wire:click="volver"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-6 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                        Importar otro archivo
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
