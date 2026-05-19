<x-layouts.app>
    @section('title', 'Mi Perfil')

    <div class="mb-6">
        <h2 class="text-xl sm:text-2xl font-medium text-slate-900">Mi Perfil</h2>
        <p class="mt-1 text-sm text-slate-500">Información de tu cuenta y configuración personal</p>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <div class="flex flex-col items-center">
                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gpt-100 text-2xl font-bold text-gpt-600">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
                <h3 class="mt-4 text-lg font-medium text-slate-900">{{ auth()->user()->name ?? 'Usuario' }}</h3>
                <p class="text-sm text-slate-500">{{ auth()->user()->puesto ?? 'Sin puesto' }}</p>
                <span class="mt-2">
                    @php $isActive = auth()->user()->status === 'active'; @endphp
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $isActive ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-700' }}">
                        {{ $isActive ? 'Activo' : 'Inactivo' }}
                    </span>
                </span>
            </div>
            <div class="mt-6 space-y-3 border-t border-slate-100 pt-6">
                <div class="flex items-center gap-3 text-sm">
                    <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span class="text-slate-700">{{ auth()->user()->email ?? '—' }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span class="text-slate-700">{{ auth()->user()->departamento ?? 'Sin departamento' }}</span>
                </div>
                <div class="flex items-center gap-3 text-sm">
                    <svg class="h-4 w-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span class="text-slate-700">{{ auth()->user()->puesto ?? 'Sin puesto' }}</span>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-lg border border-slate-200 bg-white p-6">
                <h3 class="text-base font-medium text-slate-900">Roles asignados</h3>
                <p class="mt-1 text-sm text-slate-500">Estos roles determinan tus permisos en el sistema</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    @php $roles = auth()->user()->roles ?? collect(); @endphp
                    @if($roles->isNotEmpty())
                        @foreach($roles as $rol)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-sm font-medium text-slate-700">
                                <svg class="h-3.5 w-3.5 text-gpt-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                {{ $rol->name }}
                            </span>
                        @endforeach
                    @else
                        <span class="text-sm text-slate-400">Sin roles asignados</span>
                    @endif
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-6">
                <h3 class="text-base font-medium text-slate-900">Información de cuenta</h3>
                <dl class="mt-4 divide-y divide-slate-100">
                    <div class="flex justify-between py-2.5">
                        <dt class="text-sm text-slate-500">ID Usuario</dt>
                        <dd class="text-sm font-medium text-slate-900">{{ auth()->user()->id ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-sm text-slate-500">Miembro desde</dt>
                        <dd class="text-sm font-medium text-slate-900">{{ auth()->user()->created_at?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-sm text-slate-500">Último acceso</dt>
                        <dd class="text-sm font-medium text-slate-900">{{ auth()->user()->last_login_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between py-2.5">
                        <dt class="text-sm text-slate-500">Estado</dt>
                        <dd class="text-sm font-medium text-slate-900">{{ ucfirst(auth()->user()->status ?? '—') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</x-layouts.app>
