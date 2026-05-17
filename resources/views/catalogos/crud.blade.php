<x-layouts.app>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-medium text-slate-900">Catálogos</h2>
            <p class="mt-1 text-sm text-slate-500">Administración de tablas de referencia del sistema</p>
        </div>
    </x-slot>

    @php
        $activeTab = request('tab', 'tech_references');
        if (!array_key_exists($activeTab, $catalogos)) {
            $activeTab = array_key_first($catalogos);
        }
    @endphp

    <div
        x-data="catalogosCrud(@js($activeTab))"
        class="space-y-4"
    >
        {{-- Flash messages --}}
        @if(session('success'))
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 4000)"
            class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"
        >
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            @foreach($errors->all() as $error)
                <div>• {{ $error }}</div>
            @endforeach
        </div>
        @endif

        {{-- Tabs --}}
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-wrap gap-1 border-b border-slate-200 bg-slate-50/60 p-1.5 overflow-x-auto">
                @foreach($catalogos as $slug => $cat)
                <button
                    type="button"
                    @click="activeTab = @js($slug)"
                    :class="activeTab === @js($slug)
                        ? 'bg-white text-gpt-700 shadow-sm border-slate-200'
                        : 'text-slate-500 hover:text-slate-700 border-transparent'"
                    class="inline-flex items-center gap-2 whitespace-nowrap rounded-lg border px-3.5 py-1.5 text-xs font-medium transition-all"
                >
                    {{ $cat['label'] }}
                    <span
                        :class="activeTab === @js($slug) ? 'bg-gpt-100 text-gpt-700' : 'bg-slate-200 text-slate-600'"
                        class="inline-flex h-5 min-w-[20px] items-center justify-center rounded-full px-1.5 text-[10px] font-semibold transition-colors"
                    >
                        {{ $cat['records']->count() }}
                    </span>
                </button>
                @endforeach
            </div>

            {{-- Tab content --}}
            @foreach($catalogos as $slug => $cat)
            <div x-show="activeTab === @js($slug)" x-cloak>
                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">{{ $cat['label'] }}</h3>
                        <p class="text-[11px] text-slate-500">{{ $cat['records']->count() }} registros activos</p>
                    </div>
                    <button
                        type="button"
                        @click="openCreate(@js($slug), @js($cat['form']))"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm transition-colors hover:bg-gpt-700"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                        </svg>
                        Nuevo
                    </button>
                </div>

                {{-- Table --}}
                <div class="overflow-auto" style="max-height: calc(100vh - 320px);">
                    @if($cat['records']->isEmpty())
                        <div class="flex flex-col items-center justify-center py-14 text-center">
                            <svg class="mb-2 h-10 w-10 text-slate-200" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                            </svg>
                            <p class="text-sm text-slate-500">Sin registros activos.</p>
                            <p class="text-[11px] text-slate-400">Usa el botón «Nuevo» para crear el primero.</p>
                        </div>
                    @else
                        <table class="min-w-full">
                            <thead class="sticky top-0 z-10 bg-slate-50">
                                <tr>
                                    @foreach($cat['columns'] as $col)
                                    <th class="whitespace-nowrap px-4 py-2.5 text-left text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                        {{ $col['label'] }}
                                    </th>
                                    @endforeach
                                    <th class="sticky right-0 bg-slate-50 px-4 py-2.5 text-right text-[10px] font-semibold uppercase tracking-wide text-slate-500">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($cat['records'] as $record)
                                <tr class="group hover:bg-slate-50/60">
                                    @foreach($cat['columns'] as $col)
                                    @php
                                        $value = data_get($record, $col['key']);
                                    @endphp
                                    <td class="max-w-[220px] truncate px-4 py-2 text-xs text-slate-700" title="{{ $value }}">
                                        {{ $value }}
                                    </td>
                                    @endforeach
                                    <td class="sticky right-0 bg-white px-4 py-2 text-right text-xs group-hover:bg-slate-50/60">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button
                                                type="button"
                                                @click="openEdit(@js($slug), @js($cat['form']), @js($record->toArray()))"
                                                class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-2 py-1 text-[11px] font-medium text-slate-600 transition-colors hover:bg-slate-50"
                                            >
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125"/></svg>
                                                Editar
                                            </button>
                                            <button
                                                type="button"
                                                @click="confirmDelete(@js($slug), {{ $record->id }}, @js($cat['label']))"
                                                class="inline-flex items-center gap-1 rounded-md border border-red-200 bg-white px-2 py-1 text-[11px] font-medium text-red-600 transition-colors hover:bg-red-50"
                                            >
                                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                                Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- ───────────────────────── Form Modal (create + edit) ───────────────────────── --}}
        <div
            x-show="formModal.open"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeForm()"></div>

            <div
                class="relative z-10 w-full max-w-2xl max-h-[90vh] flex flex-col rounded-2xl bg-white shadow-2xl"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-base font-semibold text-slate-800">
                            <span x-text="formModal.mode === 'edit' ? 'Editar registro' : 'Nuevo registro'"></span>
                        </h3>
                        <p class="mt-0.5 text-xs text-slate-500" x-text="formModal.tabLabel"></p>
                    </div>
                    <button type="button" @click="closeForm()" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form
                    :action="formAction()"
                    method="POST"
                    class="flex-1 overflow-y-auto px-6 py-5"
                    @submit="submitting = true"
                >
                    @csrf
                    <template x-if="formModal.mode === 'edit'">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <template x-for="field in formModal.fields" :key="field.name">
                            <div :class="field.type === 'textarea' ? 'sm:col-span-2' : ''">
                                <label class="block text-xs font-medium text-slate-700 mb-1">
                                    <span x-text="field.label"></span>
                                    <span x-show="field.required" class="text-red-500">*</span>
                                </label>

                                <template x-if="field.type === 'text'">
                                    <input
                                        type="text"
                                        :name="field.name"
                                        :value="formModal.values[field.name] ?? ''"
                                        :required="field.required"
                                        class="block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-gpt-500 focus:outline-none focus:ring-1 focus:ring-gpt-500"
                                    >
                                </template>

                                <template x-if="field.type === 'number'">
                                    <input
                                        type="number"
                                        :name="field.name"
                                        :step="field.step ?? '1'"
                                        :value="formModal.values[field.name] ?? ''"
                                        :required="field.required"
                                        class="block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-gpt-500 focus:outline-none focus:ring-1 focus:ring-gpt-500"
                                    >
                                </template>

                                <template x-if="field.type === 'textarea'">
                                    <textarea
                                        :name="field.name"
                                        rows="3"
                                        :required="field.required"
                                        class="block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-gpt-500 focus:outline-none focus:ring-1 focus:ring-gpt-500"
                                        x-text="formModal.values[field.name] ?? ''"
                                    ></textarea>
                                </template>

                                <template x-if="field.type === 'select'">
                                    <select
                                        :name="field.name"
                                        :required="field.required"
                                        class="block w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:border-gpt-500 focus:outline-none focus:ring-1 focus:ring-gpt-500"
                                    >
                                        <option value="">— sin asignar —</option>
                                        <template x-for="(label, value) in (field.options ?? {})" :key="value">
                                            <option
                                                :value="value"
                                                :selected="String(formModal.values[field.name] ?? '') === String(value)"
                                                x-text="label"
                                            ></option>
                                        </template>
                                    </select>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div class="mt-6 flex justify-end gap-2 border-t border-slate-100 pt-4">
                        <button
                            type="button"
                            @click="closeForm()"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50"
                        >
                            Cancelar
                        </button>
                        <button
                            type="submit"
                            :disabled="submitting"
                            class="rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-gpt-700 disabled:opacity-50"
                        >
                            <span x-text="formModal.mode === 'edit' ? 'Actualizar' : 'Crear'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ───────────────────────── Delete confirmation modal ───────────────────────── --}}
        <div
            x-show="deleteModal.open"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
        >
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeDelete()"></div>

            <div
                class="relative z-10 w-full max-w-md rounded-2xl bg-white shadow-2xl"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
            >
                <div class="flex items-start gap-3 px-6 pt-6">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-semibold text-slate-900">¿Eliminar este registro?</h3>
                        <p class="mt-1 text-sm text-slate-600">
                            El registro de <span class="font-medium" x-text="deleteModal.label"></span> se ocultará del sistema.
                            Por seguridad, no se borrará físicamente para no romper relaciones existentes.
                        </p>
                    </div>
                </div>

                <form :action="deleteAction()" method="POST" class="flex justify-end gap-2 px-6 pb-6 pt-5">
                    @csrf
                    <input type="hidden" name="_method" value="DELETE">
                    <button
                        type="button"
                        @click="closeDelete()"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-red-700"
                    >
                        Sí, eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function catalogosCrud(initialTab) {
            return {
                activeTab: initialTab,
                submitting: false,
                formModal: {
                    open: false,
                    mode: 'create',
                    tab: null,
                    tabLabel: '',
                    fields: [],
                    values: {},
                    id: null,
                },
                deleteModal: {
                    open: false,
                    tab: null,
                    id: null,
                    label: '',
                },

                openCreate(slug, fields) {
                    this.formModal = {
                        open: true,
                        mode: 'create',
                        tab: slug,
                        tabLabel: this.labelOf(slug),
                        fields: fields,
                        values: {},
                        id: null,
                    };
                    this.submitting = false;
                },

                openEdit(slug, fields, record) {
                    this.formModal = {
                        open: true,
                        mode: 'edit',
                        tab: slug,
                        tabLabel: this.labelOf(slug),
                        fields: fields,
                        values: record,
                        id: record.id,
                    };
                    this.submitting = false;
                },

                closeForm() {
                    this.formModal.open = false;
                    this.submitting = false;
                },

                confirmDelete(slug, id, label) {
                    this.deleteModal = { open: true, tab: slug, id: id, label: label };
                },

                closeDelete() {
                    this.deleteModal.open = false;
                },

                formAction() {
                    if (!this.formModal.tab) return '#';
                    if (this.formModal.mode === 'edit') {
                        return `/catalogos-admin/${this.formModal.tab}/${this.formModal.id}`;
                    }
                    return `/catalogos-admin/${this.formModal.tab}`;
                },

                deleteAction() {
                    if (!this.deleteModal.tab) return '#';
                    return `/catalogos-admin/${this.deleteModal.tab}/${this.deleteModal.id}`;
                },

                labelOf(slug) {
                    const labels = @js(collect($catalogos)->map(fn($c) => $c['label'])->all());
                    return labels[slug] ?? slug;
                },
            };
        }
    </script>
</x-layouts.app>
