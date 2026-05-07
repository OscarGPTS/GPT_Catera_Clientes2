<x-layouts.app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-medium text-slate-900">RH Mapping</h2>
                <p class="mt-1 text-sm text-slate-500">Correspondencia entre puestos de RH y roles del sistema</p>
            </div>
            <button type="button" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Nuevo mapeo
            </button>
        </div>
    </x-slot>

    <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Puesto RH</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Rol Sistema</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Prioridad</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Activo</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($rhMappings ?? [] as $mapping)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900">{{ $mapping->puesto_rh }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                                <x-badge :label="$mapping->rol_sistema" variant="info" />
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $mapping->prioridad }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <form method="POST" action="{{ route('admin.rh-mapping.toggle', $mapping) }}">
                                    @csrf
                                    <button type="submit" class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-gpt-600 focus:ring-offset-2 {{ $mapping->activo ? 'bg-emerald-500' : 'bg-slate-200' }}">
                                        <span class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform {{ $mapping->activo ? 'translate-x-5' : 'translate-x-0.5' }}"></span>
                                    </button>
                                </form>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:text-gpt-600 hover:bg-slate-100 transition-colors" title="Editar">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:text-gpt-red-600 hover:bg-red-50 transition-colors" title="Eliminar">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center">
                                <x-empty-state
                                    title="Sin mapeos RH"
                                    description="No se han definido mapeos entre puestos de RH y roles del sistema."
                                    icon="link"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(($rhMappings ?? null) && method_exists($rhMappings, 'links'))
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $rhMappings->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
