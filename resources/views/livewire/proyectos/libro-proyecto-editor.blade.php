<div>
    @section('title', 'Libro de Proyecto — ' . ($proyecto->cp_numero ?? 'CP'))

    <div>
        <nav class="flex items-center gap-2 text-sm text-slate-500 mb-2" aria-label="Breadcrumb">
            <a href="{{ route('proyectos.index') }}" class="hover:text-slate-700 transition-colors">Proyectos</a>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-slate-900">{{ $proyecto->cp_numero }}</span>
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium text-slate-900">Libro de Proyecto</span>
        </nav>
        <h2 class="text-xl sm:text-2xl font-medium text-slate-900">Libro de Proyecto — {{ $proyecto->cliente->razon_social ?? '—' }}</h2>
        <p class="mt-1 text-sm text-slate-500">Dossier ISO — Control documental de ejecución</p>
    </div>

    @if($successMessage)
        <div wire:transition.opacity.duration.500ms class="mt-4 rounded-lg bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-800">{{ $successMessage }}</div>
    @endif

    {{-- Header: project name, % avance global --}}
    <div class="mt-6 rounded-lg border border-slate-200 bg-white p-6 mb-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-sm font-medium text-slate-500">Avance global del dossier</h3>
                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-4xl font-bold text-slate-900">{{ number_format($porcentajeGlobal, 0) }}%</span>
                    <span class="text-sm text-slate-400">({{ $itemsCompletados }}/{{ $itemsTotales }} items)</span>
                </div>
            </div>
            <div class="text-right">
                <div class="w-48 h-4 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-gpt-600 transition-all duration-500" style="width: {{ $porcentajeGlobal }}%"></div>
                </div>
                @if($porcentajeGlobal >= 100)
                    <button type="button" class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        Generar PDF consolidado
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Accordion Sections --}}
    <div class="space-y-3">
        @foreach($secciones as $index => $seccion)
            <div class="rounded-lg border shadow-sm transition-colors {{ $seccion['completada'] ? 'border-l-4 border-l-green-500 border-slate-200 bg-white' : 'border-slate-200 bg-white' }}">
                {{-- Accordion Header --}}
                <button type="button" wire:click="toggleSeccion({{ $index }})" class="flex items-center w-full px-5 py-4 text-left hover:bg-slate-50 transition-colors">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-sm font-bold text-white mr-4">{{ $seccion['codigo'] }}</span>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-slate-900">{{ $seccion['nombre'] }}</span>
                            @if($seccion['completada'])
                                <svg class="h-4 w-4 text-green-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            @endif
                        </div>
                        <div class="mt-1 flex items-center gap-3">
                            <div class="h-2 w-32 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-full rounded-full bg-gpt-600 transition-all duration-300" style="width: {{ $seccion['porcentaje'] }}%"></div>
                            </div>
                            <span class="text-xs text-slate-500">{{ number_format($seccion['porcentaje'], 0) }}%</span>
                        </div>
                    </div>

                    <svg class="h-5 w-5 text-slate-400 transition-transform duration-200 shrink-0 ml-4 {{ $seccion['abierta'] ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>

                {{-- Accordion Body --}}
                @if($seccion['abierta'])
                    <div class="border-t border-slate-100 px-5 py-5 space-y-6">
                        {{-- Checklist --}}
                        <div>
                            <h4 class="text-sm font-semibold text-slate-900 mb-3">Checklist de entregables</h4>
                            <div class="space-y-2">
                                @foreach($seccion['checklist'] as $itemIndex => $item)
                                    <label class="flex items-start gap-3 p-2 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer">
                                        <input type="checkbox"
                                               wire:click="toggleChecklist({{ $index }}, {{ $itemIndex }})"
                                               {{ $item['completado'] ? 'checked' : '' }}
                                               class="mt-0.5 h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                                        <span class="text-sm {{ $item['completado'] ? 'text-slate-400 line-through' : 'text-slate-700' }}">{{ $item['descripcion'] }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Uploaded docs list --}}
                        <div class="border-t border-slate-100 pt-5">
                            <h4 class="text-sm font-semibold text-slate-900 mb-3">Documentos cargados</h4>
                            @if(count($seccion['documentos']) > 0)
                                <div class="space-y-2">
                                    @foreach($seccion['documentos'] as $doc)
                                        <div class="flex items-center justify-between rounded-lg border border-slate-100 bg-slate-50 px-4 py-3 group">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <svg class="h-8 w-8 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                                <div class="min-w-0">
                                                    <p class="text-sm font-medium text-slate-900 truncate">{{ $doc['nombre'] }}</p>
                                                    <p class="text-xs text-slate-400">v{{ $doc['version'] }} &middot; {{ $doc['tamano'] }} &middot; {{ $doc['subidoPor'] }} &middot; {{ $doc['fecha'] }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-slate-400 text-center py-4">Sin documentos cargados en esta sección</p>
                            @endif

                            <div class="mt-3 rounded-lg border-2 border-dashed border-slate-200 p-6 text-center hover:border-gpt-600 transition-colors cursor-pointer">
                                <svg class="mx-auto h-8 w-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m0 0L8 8m4-4l4 4M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1"/></svg>
                                <p class="mt-2 text-sm text-slate-500">Arrastra archivos aquí o haz clic para seleccionar</p>
                                <p class="text-xs text-slate-400 mt-0.5">PDF, Word, Excel, DWG — máx. 25 MB</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Bottom actions --}}
    <div class="mt-6 flex items-center justify-end gap-3">
        <button type="button" wire:click="guardarAvance" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors" wire:loading.attr="disabled">
            Guardar avance
        </button>
        <button type="button" class="rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
            Exportar estatus
        </button>
    </div>
</div>