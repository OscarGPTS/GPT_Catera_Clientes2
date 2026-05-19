<aside
    class="fixed inset-y-0 left-0 z-50 flex h-full w-64 flex-col bg-slate-900 shadow-lg shadow-slate-950/20 transition-[width,transform] duration-300 ease-[cubic-bezier(0.4,0,0.2,1)] lg:static lg:inset-auto lg:z-auto lg:h-auto lg:shrink-0 lg:translate-x-0"
    style="will-change: width, transform;"
    :class="{
        '-translate-x-full': !sidebarMobileOpen,
        'translate-x-0': sidebarMobileOpen,
        'lg:w-64': sidebarOpen,
        'lg:w-[68px]': !sidebarOpen
    }"
    @sidebar-toggle.window="sidebarOpen = !sidebarOpen; localStorage.setItem('sidebar_open', sidebarOpen)"
    x-data="{
        activeGroup: null,
        toggleGroup(group) { this.activeGroup = this.activeGroup === group ? null : group }
    }"
>
    {{-- Subtle top gradient accent --}}
    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-gpt-600 via-gpt-500 to-gpt-red-600 opacity-80"></div>

    {{-- Brand area --}}
    <div class="flex h-16 items-center border-b border-slate-800/80 px-4">
        <div class="flex items-center gap-3 overflow-hidden">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-gpt-500 to-gpt-700 shadow-sm shadow-gpt-900/30">
                <span class="text-sm font-bold text-white">GPT</span>
            </div>
            <div class="min-w-0 flex-1 transition-opacity duration-200" :class="{ 'opacity-0 w-0': !sidebarOpen && !sidebarMobileOpen, 'opacity-100': sidebarOpen || sidebarMobileOpen }">
                <p class="text-sm font-semibold leading-tight text-white tracking-tight">GPT Services</p>
                <p class="text-[10px] leading-tight text-slate-500 tracking-wide">Platform</p>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3" @scroll.window.passive="">
        {{-- Principal --}}
        <x-sidebar-section label="Principal">
            <x-sidebar-item href="/" icon="home" label="Dashboard" />
            {{-- <x-sidebar-item href="/pipeline" icon="presentation-chart-line" label="Pipeline Global" /> --}}
            <x-sidebar-item href="/oportunidades" icon="briefcase" label="Oportunidades" />
            <x-sidebar-item href="/clientes" icon="building-office" label="Clientes" />
        </x-sidebar-section>

        {{-- Catálogos --}}
        @php
        $catalogosItems = ['/catalogos-admin', '/catalogos'];
        @endphp
        <x-sidebar-section label="Catálogos" collapsible>
            <x-sidebar-item href="/catalogos-admin" icon="clipboard-document-list" label="Administrar" :siblingHrefs="$catalogosItems" />
            <x-sidebar-item href="/catalogos" icon="circle-stack" label="Importar Ext." :siblingHrefs="$catalogosItems" />
        </x-sidebar-section>

        {{-- Proyectos --}}
        @canany(['ver proyectos', 'ver libro proyecto'])
        @php
        $proyectosItems = ['/proyectos', '/proyectos/asignaciones', '/proyectos/bom-boe', '/proyectos/suministros'];
        @endphp
        <x-sidebar-section label="Proyectos" collapsible>
            @can('ver proyectos')
            <x-sidebar-item href="/proyectos" icon="clipboard-document-list" label="Proyectos" :siblingHrefs="$proyectosItems" />
            @endcan
            @can('ver libro proyecto')
            <x-sidebar-item href="/proyectos/asignaciones" icon="chart-bar" label="Asignaciones" :siblingHrefs="$proyectosItems" />
            @endcan
            <x-sidebar-item href="/proyectos/bom-boe" icon="cog-6-tooth" label="BOM / BOE" :siblingHrefs="$proyectosItems" />
            <x-sidebar-item href="/proyectos/suministros" icon="truck" label="Suministros" :siblingHrefs="$proyectosItems" />
        </x-sidebar-section>
        @endcanany

        {{-- Operaciones --}}
        @canany(['ver bitacora', 'solicitar viaticos'])
        <x-sidebar-section label="Operaciones" collapsible>
            @can('solicitar viaticos')
            <x-sidebar-item href="/viaticos" icon="currency-dollar" label="Viáticos" />
            @endcan
            @can('ver bitacora')
            <x-sidebar-item href="/bitacora" icon="document-text" label="Bitácora" />
            @endcan
        </x-sidebar-section>
        @endcanany

        {{-- Finanzas --}}
        @canany(['ver finanzas'])
        @php
        $finanzasItems = ['/finanzas', '/finanzas/cierres'];
        @endphp
        <x-sidebar-section label="Finanzas" collapsible>
            <x-sidebar-item href="/finanzas" icon="banknotes" label="Finanzas" :siblingHrefs="$finanzasItems" />
            <x-sidebar-item href="/finanzas/cierres" icon="calculator" label="Cierres" :siblingHrefs="$finanzasItems" />
        </x-sidebar-section>
        @endcanany

        {{-- Chat --}}
        <x-sidebar-section label="Chat">
            <x-sidebar-item href="/chat" icon="chat-bubble-left-right" label="Chat" :badge="\App\Livewire\Chat\ChatSidebarChannels::getUnreadCount()" />
        </x-sidebar-section>

        {{-- Ejecutivo --}}
        @canany(['ver vista ejecutiva'])
        <x-sidebar-section label="Ejecutivo">
            <x-sidebar-item href="/ejecutivo" icon="presentation-chart-line" label="Dashboard Ejecutivo" />
        </x-sidebar-section>
        @endcanany

        {{-- Admin --}}
        @canany(['ver admin usuarios'])
        <x-sidebar-section label="Admin" collapsible>
            <x-sidebar-item href="/admin/usuarios" icon="users" label="Usuarios" />
            <x-sidebar-item href="/admin/socios" icon="star" label="Socios" />
            <x-sidebar-item href="/admin/rh-mapping" icon="building-office" label="Mapeo RH" />
            <x-sidebar-item href="/admin/roles-permisos" icon="shield-check" label="Roles" />
        </x-sidebar-section>
        @endcanany
    </nav>

    {{-- User footer --}}
    <div class="border-t border-slate-800/80 bg-slate-900" x-data="{ footerOpen: false }">
        <button 
            @@click="footerOpen = !footerOpen"
            class="flex w-full items-center gap-3 px-3 py-3 text-left transition-colors hover:bg-slate-800/60"
        >
            <div class="relative shrink-0">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-gpt-500 to-gpt-700 text-xs font-semibold text-white shadow-sm">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
                <span class="absolute -bottom-0.5 -right-0.5 flex h-3 w-3 rounded-full border-2 border-slate-900 bg-green-500" title="En línea"></span>
            </div>
            <div class="min-w-0 flex-1" :class="{ 'hidden': !sidebarOpen && !sidebarMobileOpen }">
                <p class="truncate text-[13px] font-medium leading-tight text-white">{{ auth()->user()->name ?? 'Usuario' }}</p>
                <p class="truncate text-[11px] leading-tight text-slate-400">{{ auth()->user()->email ?? '' }}</p>
            </div>
            <svg :class="{ 'hidden': !sidebarOpen && !sidebarMobileOpen }" class="h-4 w-4 shrink-0 text-slate-500 transition-transform duration-200" :class="footerOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <div x-show="footerOpen" x-cloak class="border-t border-slate-800/60 bg-slate-800/40 px-2 py-1">
            <a href="/perfil" class="flex items-center gap-3 rounded-md px-3 py-1.5 text-[13px] text-slate-400 transition-colors hover:bg-slate-700/50 hover:text-white">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                <span :class="{ 'hidden': !sidebarOpen && !sidebarMobileOpen }">Mi perfil</span>
            </a>
            <a href="/configuracion" class="flex items-center gap-3 rounded-md px-3 py-1.5 text-[13px] text-slate-400 transition-colors hover:bg-slate-700/50 hover:text-white">
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span :class="{ 'hidden': !sidebarOpen && !sidebarMobileOpen }">Configuración</span>
            </a>
            <hr class="my-1 border-slate-800/60">
            <form method="POST" action="/logout">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-md px-3 py-1.5 text-[13px] text-slate-400 transition-colors hover:bg-gpt-red-600/20 hover:text-gpt-red-400">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span :class="{ 'hidden': !sidebarOpen && !sidebarMobileOpen }">Cerrar sesión</span>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- Backdrop for mobile --}}
<div
    x-show="sidebarMobileOpen"
    x-transition.opacity
    x-on:click="sidebarMobileOpen = false"
    class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"
    x-cloak
></div>
