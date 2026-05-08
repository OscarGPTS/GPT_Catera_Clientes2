<x-layouts.app>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-medium text-slate-900">Clientes</h2>
                <p class="mt-1 text-sm text-slate-500">Catálogo de clientes y sus contactos</p>
            </div>
            @can('create', \App\Models\Comercial\Cliente::class)
                <button type="button" @click="showCreateModal = true"
                        class="inline-flex items-center gap-2 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Nuevo cliente
                </button>
            @endcan
        </div>
    </x-slot>

    {{-- Filters --}}
    <div class="rounded-lg border border-slate-200 bg-white p-4 mb-6" x-data="{ buscar: '{{ request('buscar', '') }}' }">
        <form method="GET" action="{{ route('clientes.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[240px]">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                <input type="text" name="buscar" x-model="buscar"
                       placeholder="Buscar por razón social, alias, RFC..."
                       class="w-full rounded-lg border border-slate-200 bg-white py-2 pl-10 pr-4 text-sm text-slate-900 placeholder-slate-400 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
            </div>
            <select name="sector" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                <option value="">Todos los sectores</option>
                @foreach($sectores as $sector)
                    <option value="{{ $sector }}" {{ request('sector') === $sector ? 'selected' : '' }}>{{ $sector }}</option>
                @endforeach
            </select>
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="activo" value="0" {{ request('activo') === '0' ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                <span class="text-sm text-slate-700">Incluir inactivos</span>
            </label>
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                Buscar
            </button>
            <a href="{{ route('clientes.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">Limpiar</a>
        </form>
    </div>

    {{-- Clientes table --}}
    <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Alias</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Razón Social</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">RFC</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Sector</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Segmento</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Contactos</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Proyectos</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($clientes as $cliente)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex items-center justify-center rounded-md bg-gpt-100 px-2 py-1 text-xs font-bold text-gpt-800 uppercase">{{ $cliente->alias_3letras }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900">{{ $cliente->razon_social }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm font-mono text-slate-600">{{ $cliente->rfc ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $cliente->sector ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $cliente->segmento ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-slate-600">{{ $cliente->contactos->count() }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-center text-sm text-slate-600">{{ $cliente->proyectos_count ?? $cliente->proyectos()->count() }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if($cliente->activo)
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Activo</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">Inactivo</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <a href="{{ route('clientes.show', $cliente) }}" class="text-sm text-gpt-600 hover:text-gpt-700 transition-colors">Ver detalle</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center">
                                <x-empty-state
                                    title="Sin clientes"
                                    description="No se encontraron clientes con los filtros seleccionados."
                                    actionLabel="Nuevo cliente"
                                    :actionUrl="route('clientes.index')"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($clientes->hasMorePages())
            <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
                <p class="text-sm text-slate-500">Mostrando {{ $clientes->firstItem() }}&ndash;{{ $clientes->lastItem() }} de {{ number_format($clientes->total()) }}</p>
                {{ $clientes->appends(request()->except('page'))->links() }}
            </div>
        @endif
    </div>

    {{-- Create Modal --}}
    <div x-data="{ showCreateModal: false }" x-init="showCreateModal = false">
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50" @click.self="showCreateModal = false">
            <div class="w-full max-w-lg rounded-lg bg-white p-6 shadow-xl">
                <h3 class="text-lg font-medium text-slate-900 mb-4">Nuevo cliente</h3>
                <form method="POST" action="{{ route('clientes.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="razon_social" class="block text-sm font-medium text-slate-700 mb-1">Razón social *</label>
                            <input type="text" name="razon_social" id="razon_social" required
                                   class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600" placeholder="Nombre completo de la empresa">
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="alias_3letras" class="block text-sm font-medium text-slate-700 mb-1">Alias (3 letras) *</label>
                                <input type="text" name="alias_3letras" id="alias_3letras" required maxlength="5"
                                       class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600 uppercase" placeholder="SED">
                            </div>
                            <div>
                                <label for="rfc" class="block text-sm font-medium text-slate-700 mb-1">RFC</label>
                                <input type="text" name="rfc" id="rfc" maxlength="13"
                                       class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600 uppercase" placeholder="XAXX010101000">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="sector" class="block text-sm font-medium text-slate-700 mb-1">Sector</label>
                                <select name="sector" id="sector" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                    <option value="">Seleccionar...</option>
                                    <option value="Gobierno">Gobierno</option>
                                    <option value="Energia">Energía</option>
                                    <option value="Industrial">Industrial</option>
                                    <option value="Privado">Privado</option>
                                </select>
                            </div>
                            <div>
                                <label for="segmento" class="block text-sm font-medium text-slate-700 mb-1">Segmento</label>
                                <select name="segmento" id="segmento" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                                    <option value="">Seleccionar...</option>
                                    <option value="A">A - Estratégico</option>
                                    <option value="B">B - Crecimiento</option>
                                    <option value="C">C - Operativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showCreateModal = false" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-700">Crear cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>