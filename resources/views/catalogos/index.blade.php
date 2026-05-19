<x-layouts.app>
    <x-slot name="header">
        <div>
            <h2 class="text-xl sm:text-2xl font-medium text-slate-900">Catálogos</h2>
            <p class="mt-1 text-sm text-slate-500">Tablas de referencia para importación y codificación de datos</p>
        </div>
    </x-slot>

    {{-- Modal compartido para importación --}}
    <div
        x-data="{
            modal: null,
            reemplazar: false,
            openModal(slug, label, fields) {
                this.modal = { slug, label, fields };
                this.reemplazar = false;
            }
        }"
        class="space-y-6"
    >

        {{-- Flash / errores --}}
        @if(session('success'))
        <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
        @endif

        {{-- Grid de catálogos --}}
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2 xl:grid-cols-3">
            @foreach($catalogos as $slug => $cat)
            <div class="flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">

                {{-- Cabecera del card --}}
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3.5">
                    <div class="min-w-0">
                        <h3 class="truncate text-sm font-semibold text-slate-800">{{ $cat['label'] }}</h3>
                        <span class="mt-0.5 inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-500">
                            {{ $cat['records']->count() }} registros
                        </span>
                    </div>
                    <button
                        type="button"
                        @click="openModal('{{ $slug }}', '{{ $cat['label'] }}', {{ json_encode(array_values($cat['import_headers'])) }})"
                        class="ml-3 inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-600 shadow-sm transition-colors hover:bg-slate-50"
                    >
                        <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                        </svg>
                        Importar
                    </button>
                </div>

                {{-- Tabla de registros --}}
                <div class="flex-1 overflow-auto" style="max-height: 220px;">
                    @if($cat['records']->isEmpty())
                        <div class="flex flex-col items-center justify-center py-8 text-center">
                            <svg class="mb-2 h-8 w-8 text-slate-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h1.5C5.496 19.5 6 18.997 6 18.375m-3.75.125V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-1.5A1.125 1.125 0 0118 18.375M20.625 4.5H3.375m17.25 0c.621 0 1.125.504 1.125 1.125M20.625 4.5h-1.5C18.504 4.5 18 5.003 18 5.625m3.75-.125V5.625m0 0v1.5c0 .621-.504 1.125-1.125 1.125M3.375 4.5c-.621 0-1.125.504-1.125 1.125M3.375 4.5h1.5C5.496 4.5 6 5.003 6 5.625m-3.75-.125V5.625m0 0v1.5c0 .621.504 1.125 1.125 1.125m0 0h1.5m-1.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m1.5-3.75C5.496 8.25 6 7.746 6 7.125v-1.5M4.875 8.25C5.496 8.25 6 8.754 6 9.375v1.5m0-5.25v5.25m0-5.25C6 5.003 6.504 4.5 7.125 4.5h9.75c.621 0 1.125.504 1.125 1.125m1.125 2.625h1.5m-1.5 0A1.125 1.125 0 0118 7.125v-1.5m1.5 2.625c-.621 0-1.125.504-1.125 1.125v1.5m2.625-2.625c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125M18 5.625v5.25M7.125 12h9.75m-9.75 0A1.125 1.125 0 016 10.875M7.125 12C6.504 12 6 12.504 6 13.125m0-2.25C6 11.496 5.496 12 4.875 12M18 10.875c0 .621-.504 1.125-1.125 1.125M18 10.875c0 .621.504 1.125 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-9.75 0h9.75"/>
                            </svg>
                            <p class="text-xs text-slate-400">Sin registros.</p>
                            <p class="text-[11px] text-slate-300">Importa un archivo Excel para comenzar.</p>
                        </div>
                    @else
                        <table class="min-w-full">
                            <thead class="sticky top-0 bg-slate-50">
                                <tr>
                                    @foreach($cat['headers'] as $header)
                                    <th class="whitespace-nowrap px-4 py-2 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                        {{ $header }}
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($cat['records'] as $record)
                                <tr class="hover:bg-slate-50/60">
                                    @foreach($cat['fields'] as $field)
                                    <td class="max-w-[180px] truncate px-4 py-1.5 text-xs text-slate-700" title="{{ $record->$field }}">
                                        {{ $record->$field }}
                                    </td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

            </div>
            @endforeach
        </div>

        {{-- ── Modal de importación ─────────────────────────────────────────── --}}
        <div
            x-show="modal !== null"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="modal = null"></div>

            {{-- Panel --}}
            <div
                class="relative z-10 w-full max-w-md rounded-2xl bg-white shadow-2xl"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
            >
                {{-- Modal header --}}
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-semibold text-slate-800" x-text="'Importar: ' + (modal?.label ?? '')"></h3>
                        <p class="mt-0.5 text-xs text-slate-500">Las columnas se leerán en orden desde la columna indicada.</p>
                    </div>
                    <button type="button" @click="modal = null" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Columnas esperadas --}}
                <div class="border-b border-slate-100 bg-slate-50 px-6 py-3">
                    <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-wide text-slate-400">Columnas esperadas (en orden)</p>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="(h, idx) in (modal?.fields ?? [])" :key="idx">
                            <span class="inline-flex items-center gap-1 rounded-md bg-white px-2 py-0.5 text-[11px] font-medium text-slate-600 shadow-sm border border-slate-200">
                                <span class="font-mono text-gpt-600" x-text="String.fromCharCode(65 + idx) + ':'"></span>
                                <span x-text="h"></span>
                            </span>
                        </template>
                    </div>
                </div>

                {{-- Form --}}
                <form
                    method="POST"
                    enctype="multipart/form-data"
                    class="px-6 py-5 space-y-4"
                    :action="modal ? '/catalogos/' + modal.slug + '/import' : '#'"
                >
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Archivo Excel / CSV</label>
                        <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-200 bg-slate-50 px-4 py-5 text-center transition-colors hover:border-gpt-300 hover:bg-gpt-50/30">
                            <svg class="mb-2 h-6 w-6 text-slate-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                            </svg>
                            <span class="text-xs text-slate-500">Haz clic para seleccionar un archivo</span>
                            <span class="mt-0.5 text-[10px] text-slate-400">.xlsx, .xls, .csv — máx. 10 MB</span>
                            <input type="file" name="archivo" accept=".xlsx,.xls,.csv" required class="sr-only">
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Fila de inicio</label>
                            <input
                                type="number" name="fila_inicio" value="2" min="1" max="9999"
                                class="block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-gpt-500 focus:outline-none focus:ring-1 focus:ring-gpt-500"
                            >
                            <p class="mt-1 text-[10px] text-slate-400">1 = primera fila del archivo</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Columna de inicio</label>
                            <input
                                type="text" name="col_inicio" value="A" maxlength="2"
                                class="block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm uppercase tracking-widest focus:border-gpt-500 focus:outline-none focus:ring-1 focus:ring-gpt-500"
                                placeholder="A"
                            >
                            <p class="mt-1 text-[10px] text-slate-400">Letra de la columna (A, B…)</p>
                        </div>
                    </div>

                    <label class="flex cursor-pointer items-center gap-2.5">
                        <input
                            type="checkbox" name="reemplazar" value="1"
                            x-model="reemplazar"
                            class="h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-500"
                        >
                        <span class="text-sm text-slate-600">Reemplazar todos los registros existentes</span>
                    </label>

                    <template x-if="reemplazar">
                        <div class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-700">
                            <svg class="mt-0.5 h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                            </svg>
                            Se eliminarán todos los registros actuales antes de importar.
                        </div>
                    </template>

                    <div class="flex gap-3 pt-1">
                        <button
                            type="submit"
                            class="flex-1 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-gpt-700"
                        >
                            Importar
                        </button>
                        <button
                            type="button"
                            @click="modal = null"
                            class="flex-1 rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.app>
