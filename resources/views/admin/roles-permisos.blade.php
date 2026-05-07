<x-layouts.app>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-medium text-slate-900">Roles y Permisos</h2>
            <p class="mt-1 text-sm text-slate-500">Matriz de permisos por rol del sistema</p>
        </div>
    </x-slot>

    <div class="rounded-lg border border-slate-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="sticky left-0 z-10 bg-slate-50 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Rol</th>
                        @foreach($permisos ?? [] as $permiso)
                            <th class="px-3 py-3 text-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                                <div class="flex flex-col items-center gap-0.5">
                                    <span>{{ $permiso->nombre }}</span>
                                    <span class="text-[10px] font-normal text-slate-400 normal-case">{{ $permiso->grupo ?? '' }}</span>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($roles ?? [] as $rol)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="sticky left-0 z-10 bg-white px-4 py-3 text-sm font-medium text-slate-900">
                                <div>
                                    <span>{{ $rol->nombre }}</span>
                                    <p class="text-xs text-slate-400 font-normal">{{ $rol->descripcion ?? '' }}</p>
                                </div>
                            </td>
                            @foreach($permisos ?? [] as $permiso)
                                <td class="px-3 py-3 text-center">
                                    @if(in_array($permiso->id, $rol->permisos_ids ?? $rol->permisos?->pluck('id')?->toArray() ?? []))
                                        <svg class="mx-auto h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        <svg class="mx-auto h-5 w-5 text-slate-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($permisos ?? []) + 1 }}" class="px-4 py-12 text-center">
                                <x-empty-state
                                    title="Sin roles definidos"
                                    description="No se encontraron roles en el sistema. Configure los roles y permisos para comenzar."
                                    icon="shield"
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(($permisos ?? null) && count($permisos) === 0 && ($roles ?? null) && count($roles) === 0)
        <div class="mt-6 rounded-lg border border-slate-200 bg-white p-12">
            <x-empty-state
                title="Sin configuración de permisos"
                description="No hay roles ni permisos configurados en el sistema. Contacte al administrador."
                icon="lock-closed"
            />
        </div>
    @endif
</x-layouts.app>
