<x-layouts.app>
    @section('title', 'Mi Perfil')

    @php $isActive = auth()->user()->status === 'active'; @endphp

    {{-- ── Cover + Avatar Header ─────────────────────────────────── --}}
    <div class="rounded-2xl overflow-hidden border border-slate-200 bg-white shadow-sm -mx-3 -mt-3 sm:-mx-4 sm:-mt-4 lg:-mx-6 lg:-mt-6 xl:-mx-8 xl:-mt-8 mb-6">

        {{-- Cover photo --}}
        <div class="relative h-44 sm:h-52 lg:h-64"
             style="background-image: url('{{ asset('img/bg1.png') }}'); background-size: cover; background-position: center;">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/30 to-transparent"></div>
            {{-- Company watermark --}}
            <div class="absolute top-4 right-4 sm:top-5 sm:right-6">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-white/25 bg-black/30 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-white/80 backdrop-blur-sm">
                    GPT Services
                </span>
            </div>
        </div>

        {{-- Profile identity row --}}
        <div class="px-5 sm:px-7 pb-5 sm:pb-6">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">

                {{-- Avatar + name --}}
                <div class="flex flex-col sm:flex-row sm:items-end gap-4 -mt-14 sm:-mt-16">
                    {{-- Avatar circle --}}
                    <div class="relative shrink-0">
                        <div class="flex h-24 w-24 sm:h-28 sm:w-28 items-center justify-center rounded-full border-4 border-white bg-gradient-to-br from-gpt-500 to-gpt-700 text-3xl sm:text-4xl font-bold text-white shadow-lg">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                        </div>
                        <span class="absolute bottom-1.5 right-1.5 flex h-4 w-4 rounded-full border-2 border-white {{ $isActive ? 'bg-green-500' : 'bg-slate-400' }}" title="{{ $isActive ? 'Activo' : 'Inactivo' }}"></span>
                    </div>

                    {{-- Name / title --}}
                    <div class="pb-1 pt-2 sm:pt-0">
                        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 leading-tight">
                            {{ auth()->user()->name ?? 'Usuario' }}
                        </h1>
                        <p class="text-sm font-medium text-slate-600 mt-0.5">{{ auth()->user()->puesto ?? 'Sin puesto' }}</p>
                        <p class="text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
                            {{ auth()->user()->departamento ?? 'Sin departamento' }}
                        </p>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="flex items-center gap-2 pb-1 self-end sm:self-auto">
                    <a href="/perfil/mi-asignacion"
                       class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Mi asignación
                    </a>
                </div>
            </div>

            {{-- Quick-info strip --}}
            <div class="mt-5 pt-4 border-t border-slate-100 flex flex-wrap items-center gap-x-5 gap-y-2">
                <span class="flex items-center gap-1.5 text-xs text-slate-500">
                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    {{ auth()->user()->email ?? '—' }}
                </span>
                @if(auth()->user()->created_at)
                <span class="flex items-center gap-1.5 text-xs text-slate-500">
                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Miembro desde {{ auth()->user()->created_at->format('d M Y') }}
                </span>
                @endif
                @if(auth()->user()->last_login_at)
                <span class="flex items-center gap-1.5 text-xs text-slate-500">
                    <svg class="h-3.5 w-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Último acceso {{ auth()->user()->last_login_at->format('d/m/Y H:i') }}
                </span>
                @endif
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $isActive ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                    <span class="mr-1 h-1.5 w-1.5 rounded-full {{ $isActive ? 'bg-green-500' : 'bg-slate-400' }}"></span>
                    {{ $isActive ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
        </div>
    </div>

    {{-- ── Content grid ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- Left: personal info cards --}}
        <div class="space-y-5">

            {{-- About --}}
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-900 mb-4">Información personal</h3>
                <div class="space-y-3.5">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100">
                            <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Puesto</p>
                            <p class="text-sm font-medium text-slate-900 truncate">{{ auth()->user()->puesto ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100">
                            <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Departamento</p>
                            <p class="text-sm font-medium text-slate-900 truncate">{{ auth()->user()->departamento ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100">
                            <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-medium uppercase tracking-wide text-slate-400">Correo electrónico</p>
                            <p class="text-sm font-medium text-slate-900 break-all">{{ auth()->user()->email ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Account details --}}
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-900 mb-3">Cuenta</h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5">
                        <span class="text-xs text-slate-500">ID de usuario</span>
                        <span class="text-xs font-semibold text-slate-800">#{{ auth()->user()->id ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5">
                        <span class="text-xs text-slate-500">Miembro desde</span>
                        <span class="text-xs font-semibold text-slate-800">{{ auth()->user()->created_at?->format('d/m/Y') ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5">
                        <span class="text-xs text-slate-500">Último acceso</span>
                        <span class="text-xs font-semibold text-slate-800">{{ auth()->user()->last_login_at?->format('d/m/Y H:i') ?? '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5">
                        <span class="text-xs text-slate-500">Estado</span>
                        <span class="text-xs font-semibold {{ $isActive ? 'text-green-700' : 'text-slate-500' }}">
                            {{ ucfirst(auth()->user()->status ?? '—') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: roles + auth providers --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Roles --}}
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gpt-50 border border-gpt-100">
                        <svg class="h-4 w-4 text-gpt-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Roles asignados</h3>
                        <p class="text-xs text-slate-500">Permisos y accesos en el sistema</p>
                    </div>
                </div>

                @php $roles = auth()->user()->roles ?? collect(); @endphp
                @if($roles->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach($roles as $rol)
                            <span class="inline-flex items-center gap-2 rounded-xl border border-gpt-200 bg-gpt-50 px-3.5 py-1.5 text-sm font-semibold text-gpt-800 shadow-sm">
                                <svg class="h-3.5 w-3.5 text-gpt-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
                                {{ $rol->name }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                            <svg class="h-6 w-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <p class="mt-2 text-sm text-slate-400">Sin roles asignados</p>
                    </div>
                @endif
            </div>

            {{-- Auth providers --}}
            @if($user->authProviders && $user->authProviders->isNotEmpty())
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-blue-50 border border-blue-100">
                        <svg class="h-4 w-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Métodos de acceso</h3>
                        <p class="text-xs text-slate-500">Proveedores de autenticación vinculados</p>
                    </div>
                </div>
                <div class="space-y-2">
                    @foreach($user->authProviders as $provider)
                    <div class="flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white shadow-sm border border-slate-200">
                                @if($provider->provider === 'google')
                                    <svg class="h-4 w-4" viewBox="0 0 24 24">
                                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                                    </svg>
                                @else
                                    <svg class="h-4 w-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-900 capitalize">{{ $provider->provider }}</p>
                                <p class="text-xs text-slate-500">{{ $provider->email ?? $provider->provider_id }}</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 rounded-full bg-green-50 border border-green-200 px-2.5 py-0.5 text-xs font-medium text-green-700">
                            <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Vinculado
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</x-layouts.app>
