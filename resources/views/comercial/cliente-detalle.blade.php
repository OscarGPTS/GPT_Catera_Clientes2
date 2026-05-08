<x-layouts.app>
    <x-slot name="header">
        <div>
            <nav class="flex items-center gap-2 text-sm text-slate-500 mb-2" aria-label="Breadcrumb">
                <a href="{{ route('clientes.index') }}" class="hover:text-slate-700 transition-colors">Clientes</a>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                <span class="font-medium text-slate-900">{{ $cliente->razon_social }}</span>
            </nav>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-medium text-slate-900">{{ $cliente->razon_social }}</h2>
                @if($cliente->activo)
                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Activo</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">Inactivo</span>
                @endif
            </div>
            <p class="mt-1 text-sm text-slate-500">{{ $cliente->alias_3letras }}{{ $cliente->rfc ? ' — ' . $cliente->rfc : '' }}</p>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Client info --}}
            <div class="rounded-lg border border-slate-200 bg-white p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-medium text-slate-900">Información del cliente</h3>
                    @can('update', $cliente)
                        <button type="button" @click="showEditModal = true" class="text-sm font-medium text-gpt-600 hover:text-gpt-700 transition-colors">Editar</button>
                    @endcan
                </div>
                <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Razón social</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $cliente->razon_social }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Alias</dt>
                        <dd class="mt-1 text-sm">
                            <span class="inline-flex items-center rounded-md bg-gpt-100 px-2 py-0.5 text-xs font-bold text-gpt-800 uppercase">{{ $cliente->alias_3letras }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">RFC</dt>
                        <dd class="mt-1 text-sm font-mono text-slate-900">{{ $cliente->rfc ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Sector</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $cliente->sector ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-slate-500">Segmento</dt>
                        <dd class="mt-1 text-sm text-slate-900">{{ $cliente->segmento ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Contacts --}}
            <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
                <div class="border-b border-slate-200 bg-slate-50 px-6 py-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-medium text-slate-900">Contactos</h3>
                        <p class="mt-0.5 text-sm text-slate-500">{{ $cliente->contactos->count() }} contacto(s)</p>
                    </div>
                    @can('update', $cliente)
                        <button type="button" @click="showContactoModal = true" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                            Agregar contacto
                        </button>
                    @endcan
                </div>
                @if($cliente->contactos->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nombre</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Puesto</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Teléfono</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">Principal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($cliente->contactos as $contacto)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900">{{ $contacto->nombre }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $contacto->puesto ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-gpt-600">{{ $contacto->email ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $contacto->telefono ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-3 text-center">
                                            @if($contacto->principal)
                                                <svg class="inline h-4 w-4 text-gpt-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <p class="text-sm text-slate-500">Sin contactos registrados.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Related projects --}}
            <div class="rounded-lg border border-slate-200 bg-white p-5">
                <h3 class="text-sm font-semibold text-slate-900 mb-3">Proyectos relacionados</h3>
                @if($cliente->proyectos->count() > 0)
                    <ul class="space-y-2">
                        @foreach($cliente->proyectos as $p)
                            <li>
                                <a href="{{ route('oportunidades.show', $p) }}" class="flex items-center justify-between p-2 rounded-md hover:bg-slate-50 transition-colors">
                                    <div>
                                        <span class="text-sm font-mono font-medium text-slate-900">{{ $p->cp_numero ?? '—' }}</span>
                                        <p class="text-xs text-slate-500">{{ $p->tech_reference ?? '' }}</p>
                                    </div>
                                    <x-badge :status="$p->estado" />
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-slate-500">Sin proyectos relacionados.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Add Contact Modal --}}
    <div x-data="{ showContactoModal: false, showEditModal: false }">
        <div x-show="showContactoModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50" @click.self="showContactoModal = false">
            <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
                <h3 class="text-lg font-medium text-slate-900 mb-4">Agregar contacto</h3>
                <form method="POST" action="{{ route('clientes.add-contacto', $cliente) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label for="contacto_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre *</label>
                            <input type="text" name="nombre" id="contacto_nombre" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                        </div>
                        <div>
                            <label for="contacto_puesto" class="block text-sm font-medium text-slate-700 mb-1">Puesto</label>
                            <input type="text" name="puesto" id="contacto_puesto" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                        </div>
                        <div>
                            <label for="contacto_email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                            <input type="email" name="email" id="contacto_email" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                        </div>
                        <div>
                            <label for="contacto_telefono" class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                            <input type="text" name="telefono" id="contacto_telefono" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                        </div>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="principal" value="1" class="h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                            <span class="text-sm text-slate-700">Contacto principal</span>
                        </label>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" @click="showContactoModal = false" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-700">Agregar contacto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>