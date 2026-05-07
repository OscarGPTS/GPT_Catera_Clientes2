<x-layouts.app>
@section('title', 'Usuarios')
<div x-data="{
    search: '{{ request('buscar') }}',
    rolFilter: '{{ request('rol') }}',
    estadoFilter: '{{ request('estado') }}',
    deptoFilter: '{{ request('departamento') }}',
    drawerOpen: false,
    drawerUser: null,
    drawerTab: 'general',
    openDrawer(user) {
        this.drawerUser = user;
        this.drawerTab = 'general';
        this.drawerOpen = true;
    },
    closeDrawer() {
        this.drawerOpen = false;
        this.drawerUser = null;
    }
}" x-on:keydown.escape.window="closeDrawer()">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-medium text-slate-900">Usuarios</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ $totalActivos ?? 0 }} usuarios activos &middot; {{ $totalInvitados ?? 0 }} invitados pendientes &middot; {{ $totalSuspendidos ?? 0 }} suspendidos
            </p>
        </div>
        <div class="flex items-center gap-3">
            <x-button variant="ghost">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/></svg>
                Sincronizar con RH
            </x-button>
            <x-button variant="primary">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Invitar usuario externo
            </x-button>
        </div>
    </div>

    <div class="mt-6 rounded-lg border border-slate-200 bg-white p-6">
        <form method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <label for="buscar" class="block text-sm font-medium text-slate-700">Buscar</label>
                <input type="text" id="buscar" name="buscar" x-model="search" placeholder="Buscar por nombre, email, puesto..." class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-600">
            </div>
            <div>
                <label for="rol" class="block text-sm font-medium text-slate-700">Rol</label>
                <select id="rol" name="rol" x-model="rolFilter" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                    <option value="">Todos los roles</option>
                    <optgroup label="Dirección y Socios">
                        <option value="super_admin">Super Admin</option>
                        <option value="direccion_general">Dirección General</option>
                        <option value="socio">Socio</option>
                        <option value="comite_socios">Comité de Socios</option>
                    </optgroup>
                    <optgroup label="Comercial">
                        <option value="director_comercial">Director Comercial</option>
                        <option value="gerente_comercial">Gerente Comercial</option>
                        <option value="ejecutivo_comercial">Ejecutivo Comercial</option>
                        <option value="asistente_comercial">Asistente Comercial</option>
                    </optgroup>
                    <optgroup label="Proyectos">
                        <option value="director_proyectos">Director de Proyectos</option>
                        <option value="gerente_proyecto">Gerente de Proyecto</option>
                        <option value="coordinador_proyecto">Coordinador de Proyecto</option>
                        <option value="lider_proyecto">Líder de Proyecto</option>
                    </optgroup>
                    <optgroup label="Operaciones">
                        <option value="director_operaciones">Director de Operaciones</option>
                        <option value="gerente_operaciones">Gerente de Operaciones</option>
                        <option value="supervisor_operaciones">Supervisor de Operaciones</option>
                    </optgroup>
                    <optgroup label="Compras e Ingeniería">
                        <option value="director_compras">Director de Compras</option>
                        <option value="gerente_compras">Gerente de Compras</option>
                        <option value="ingeniero">Ingeniero</option>
                    </optgroup>
                    <optgroup label="Finanzas">
                        <option value="director_finanzas">Director de Finanzas</option>
                        <option value="gerente_finanzas">Gerente de Finanzas</option>
                        <option value="contador">Contador</option>
                    </optgroup>
                    <optgroup label="Externos">
                        <option value="invitado">Invitado</option>
                        <option value="cliente">Cliente</option>
                    </optgroup>
                </select>
            </div>
            <div>
                <label for="estado" class="block text-sm font-medium text-slate-700">Estado</label>
                <select id="estado" name="estado" x-model="estadoFilter" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                    <option value="">Todos</option>
                    <option value="activo">Activo</option>
                    <option value="invitado">Invitado</option>
                    <option value="suspendido">Suspendido</option>
                </select>
            </div>
            <div>
                <label for="departamento" class="block text-sm font-medium text-slate-700">Departamento</label>
                <select id="departamento" name="departamento" x-model="deptoFilter" class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                    <option value="">Todos</option>
                    @foreach($departamentos ?? [] as $d)
                        <option value="{{ $d->id }}" @selected(request('departamento') == $d->id)>{{ $d->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-gpt-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                    Filtrar
                </button>
                <a href="{{ route('admin.usuarios') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 transition-colors">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="mt-6 rounded-lg border border-slate-200 bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Usuario</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Roles</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Departamento</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Puesto</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Auth</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Último acceso</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($usuarios ?? [] as $usuario)
                        @php
                            $estado = $usuario->estado ?? ($usuario->activo ? 'activo' : 'suspendido');
                            $isSuspended = $estado === 'suspendido';
                            $rowClass = $isSuspended ? 'opacity-50 italic' : '';
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors {{ $rowClass }} cursor-pointer" @@click="openDrawer({{ $usuario->id }})">
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gpt-600 text-xs font-semibold text-white">
                                        {{ strtoupper(substr($usuario->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">{{ $usuario->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $usuario->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex flex-wrap items-center gap-1">
                                    @if($usuario->es_socio ?? false)
                                        <span class="inline-flex items-center gap-0.5 rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700">
                                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2l1.5 4.5H16l-3.5 2.75 1.25 4.75L10 11.5l-3.75 2.75L7.5 9.25 4 6.5h4.5L10 2z" clip-rule="evenodd"/></svg>
                                            Socio
                                        </span>
                                    @endif
                                    @php $displayRoles = ($usuario->roles ?? collect([]))->take(3); $extraRoles = ($usuario->roles ?? collect([]))->skip(3); @endphp
                                    @foreach($displayRoles as $rol)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                                            {{ $rol->nombre }}
                                            @if(!($rol->from_rh_mapping ?? true))
                                                <svg class="h-3 w-3 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" title="Rol manual"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                                            @endif
                                        </span>
                                    @endforeach
                                    @if($extraRoles->count() > 0)
                                        <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-500 cursor-help"
                                              x-data="{ tooltip: false }" x-on:mouseenter="tooltip = true" x-on:mouseleave="tooltip = false">
                                            +{{ $extraRoles->count() }} más
                                            <span x-show="tooltip" x-cloak class="absolute z-50 mt-6 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs shadow-sm">
                                                @foreach($extraRoles as $er)
                                                    <span class="block whitespace-nowrap">{{ $er->nombre }}</span>
                                                @endforeach
                                            </span>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $usuario->departamento->nombre ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $usuario->puesto ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    @php $providers = $usuario->auth_providers ?? collect([]); $providerNames = $providers->pluck('provider')->toArray(); @endphp
                                    @if(in_array('auth0', $providerNames))
                                        <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" title="Auth0"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                                    @endif
                                    @if(in_array('google', $providerNames))
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" title="Google"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.34-1.36-.34-2.09s.12-1.43.34-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                                    @endif
                                    @if(in_array('microsoft', $providerNames))
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" title="Microsoft"><rect x="1" y="1" width="10" height="10" fill="#F25022"/><rect x="13" y="1" width="10" height="10" fill="#7FBA00"/><rect x="1" y="13" width="10" height="10" fill="#00A4EF"/><rect x="13" y="13" width="10" height="10" fill="#FFB900"/></svg>
                                    @endif
                                    @if(in_array('apple', $providerNames) || in_array('appleid', $providerNames))
                                        <svg class="h-4 w-4 text-slate-900" fill="currentColor" viewBox="0 0 24 24" title="Apple"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                                    @endif
                                    @if(in_array('email', $providerNames))
                                        <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" title="Email"><rect width="18" height="14" x="3" y="5" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
                                    @endif
                                    @if(empty($providerNames))
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm {{ $usuario->last_login_at ? 'text-slate-600' : 'text-slate-400 italic' }}">
                                @if($usuario->last_login_at)
                                    hace {{ $usuario->last_login_at->diffForHumans(null, true) }}
                                @else
                                    Nunca
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if($estado === 'activo')
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Activo</span>
                                @elseif($estado === 'invitado')
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">Invitado</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gpt-red-100 px-2.5 py-0.5 text-xs font-medium text-gpt-red-800">Suspendido</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right" @@click.stop>
                                <div class="relative inline-block" x-data="{ open: false }">
                                    <button @@click="open = !open" class="rounded-lg p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm0 5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm1.5 3.5A1.5 1.5 0 1010 16a1.5 1.5 0 001.5-1.5z"/></svg>
                                    </button>
                                    <div x-show="open" @@click.away="open = false" x-cloak class="absolute right-0 z-30 mt-1 w-48 rounded-lg border border-slate-200 bg-white py-1 shadow-sm">
                                        <button @@click="open = false; openDrawer({{ $usuario->id }})" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0Z"/></svg>
                                            Ver detalle
                                        </button>
                                        <button @@click="open = false" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Editar roles
                                        </button>
                                        <button @@click="open = false" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                            @if($estado === 'suspendido')
                                                <svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                                Activar
                                            @else
                                                <svg class="h-4 w-4 text-gpt-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                Suspender
                                            @endif
                                        </button>
                                        <button @@click="open = false" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                            Resetear password
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <x-empty-state
                                    title="Sin usuarios"
                                    description="No se encontraron usuarios con los filtros seleccionados."
                                />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if(($usuarios ?? null) && method_exists($usuarios, 'links'))
            <div class="flex items-center justify-between border-t border-slate-200 px-4 py-3">
                <div class="flex items-center gap-2 text-sm text-slate-600">
                    <span>Mostrar</span>
                    <select class="rounded-md border border-slate-200 bg-white px-2 py-1 text-sm focus:border-gpt-600 focus:ring-gpt-600">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>por página</span>
                </div>
                {{ $usuarios->links() }}
            </div>
        @endif
    </div>

    <div x-show="drawerOpen" x-cloak class="fixed inset-0 z-50 overflow-hidden" aria-labelledby="drawer-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-slate-900/50" @@click="closeDrawer()" x-transition.opacity></div>

        <div class="fixed inset-y-0 right-0 z-50 flex w-full max-w-[480px] flex-col bg-white shadow-xl" x-show="drawerOpen" x-transition:enter="transform transition ease-in-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <h2 class="text-lg font-medium text-slate-900" id="drawer-title">Detalle de usuario</h2>
                </div>
                <button @@click="closeDrawer()" class="rounded-lg p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="border-b border-slate-200 px-6">
                <nav class="-mb-px flex space-x-6">
                    <button @@click="drawerTab = 'general'" :class="drawerTab === 'general' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors">
                        General
                    </button>
                    <button @@click="drawerTab = 'roles'" :class="drawerTab === 'roles' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors">
                        Roles
                    </button>
                    <button @@click="drawerTab = 'auth'" :class="drawerTab === 'auth' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors">
                        Auth
                    </button>
                    <button @@click="drawerTab = 'auditoria'" :class="drawerTab === 'auditoria' ? 'border-gpt-600 text-gpt-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap border-b-2 px-1 py-3 text-sm font-medium transition-colors">
                        Auditoría
                    </button>
                </nav>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <div x-show="drawerTab === 'general'">
                    <div class="flex items-center gap-4 mb-6">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-gpt-600 text-xl font-semibold text-white">
                            {{ strtoupper(substr($usuario->name ?? 'U', 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-slate-900">{{ $usuario->name ?? '—' }}</h3>
                            <p class="text-sm text-slate-500">{{ $usuario->email ?? '—' }}</p>
                        </div>
                    </div>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs font-medium uppercase text-slate-500">ID Empleado</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $usuario->employee_id ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase text-slate-500">Departamento</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $usuario->departamento->nombre ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase text-slate-500">Puesto</dt>
                            <dd class="mt-1 text-sm text-slate-900">{{ $usuario->puesto ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase text-slate-500">Estado</dt>
                            <dd class="mt-1">
                                @php $dEstado = $usuario->estado ?? ($usuario->activo ? 'activo' : 'suspendido'); @endphp
                                @if($dEstado === 'activo')
                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Activo</span>
                                @elseif($dEstado === 'invitado')
                                    <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">Invitado</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-gpt-red-100 px-2.5 py-0.5 text-xs font-medium text-gpt-red-800">Suspendido</span>
                                @endif
                            </dd>
                        </div>
                        @if($usuario->es_socio ?? false)
                        <div>
                            <dd class="mt-1">
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-sm font-medium text-amber-700">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2l1.5 4.5H16l-3.5 2.75 1.25 4.75L10 11.5l-3.75 2.75L7.5 9.25 4 6.5h4.5L10 2z" clip-rule="evenodd"/></svg>
                                    Es socio
                                </span>
                            </dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <div x-show="drawerTab === 'roles'">
                    <p class="text-sm text-slate-500 mb-4">Roles asignados al usuario. Los cambios se guardan automáticamente.</p>
                    <div class="space-y-2">
                        @foreach($rolesDisponibles ?? [] as $role)
                            <label class="flex items-center gap-3 rounded-lg border border-slate-200 px-4 py-3 hover:bg-slate-50 cursor-pointer transition-colors">
                                <input type="checkbox" class="h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600" @checked(in_array($role->id, $usuario->roles->pluck('id')->toArray() ?? []))>
                                <div>
                                    <span class="text-sm font-medium text-slate-900">{{ $role->nombre }}</span>
                                    @if(!($role->from_rh_mapping ?? true))
                                        <span class="ml-1.5 inline-flex items-center rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-medium text-amber-700">Manual</span>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div x-show="drawerTab === 'auth'">
                    <p class="text-sm text-slate-500 mb-4">Proveedores de autenticación vinculados.</p>
                    @php $authProviders = $usuario->auth_providers ?? collect([]); @endphp
                    @if($authProviders->count() > 0)
                        <div class="space-y-3">
                            @foreach($authProviders as $ap)
                                <div class="flex items-center justify-between rounded-lg border border-slate-200 px-4 py-3">
                                    <div>
                                        <p class="text-sm font-medium text-slate-900 capitalize">{{ $ap->provider }}</p>
                                        <p class="text-xs text-slate-500">Vinculado {{ $ap->created_at?->format('d/m/Y') ?? '—' }} &middot; Último uso {{ $ap->last_used_at?->format('d/m/Y') ?? '—' }}</p>
                                    </div>
                                    <button class="rounded-lg p-1.5 text-slate-400 hover:text-gpt-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic">Sin proveedores de autenticación.</p>
                    @endif
                </div>

                <div x-show="drawerTab === 'auditoria'">
                    <p class="text-sm text-slate-500 mb-4">Historial de actividad del usuario.</p>
                    @php $auditLogs = $usuario->audit_logs ?? collect([]); @endphp
                    @if($auditLogs->count() > 0)
                        <div class="space-y-0">
                            @foreach($auditLogs as $log)
                                <div class="relative flex gap-3 pb-4">
                                    <div class="flex flex-col items-center">
                                        <span class="flex h-2.5 w-2.5 rounded-full {{ match($log->tipo ?? '') { 'cambio_rol' => 'bg-gpt-600', 'cambio_estado' => 'bg-amber-500', 'login' => 'bg-green-500', default => 'bg-slate-300' } }}"></span>
                                        <span class="w-px flex-1 bg-slate-200"></span>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-slate-900">{{ $log->descripcion ?? 'Sin descripción' }}</p>
                                        <p class="text-xs text-slate-400">{{ $log->created_at?->diffForHumans() ?? '—' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic">Sin registros de auditoría.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</x-layouts.app>
