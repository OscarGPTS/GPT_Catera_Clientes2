<header class="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-4 lg:px-6">
    <div class="flex items-center gap-3">
        <button
            @@click="sidebarMobileOpen = !sidebarMobileOpen; if (window.innerWidth >= 1024) { sidebarOpen = !sidebarOpen; localStorage.setItem('sidebar_open', sidebarOpen) }"
            class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-600"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <div class="hidden sm:block">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="search" placeholder="Buscar proyectos, clientes..." class="w-64 rounded-md border border-slate-200 bg-slate-50 py-1.5 pl-10 pr-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200 lg:w-80">
            </div>
        </div>
    </div>

    <div class="flex items-center gap-2">
        @can('crear oportunidad')
        <a href="/oportunidades/nueva" class="hidden items-center gap-1.5 rounded-md bg-gpt-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-700 sm:inline-flex">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nueva oportunidad
        </a>
        @endcan

        <livewire:notificaciones.notifications-dropdown />

        <button
            @@click="Livewire.dispatch('openChatDrawer')"
            class="relative rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-600"
        >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </button>

        <div class="relative" x-data="{ open: false }">
            <button @@click="open = !open" class="flex items-center gap-2 rounded-md p-1 hover:bg-slate-100">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gpt-600 text-xs font-medium text-white">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </div>
                <span class="hidden text-sm font-medium text-slate-700 sm:block">{{ auth()->user()->name ?? 'Usuario' }}</span>
            </button>
            <div x-show="open" @@click.away="open = false" x-cloak class="absolute right-0 z-50 mt-2 w-48 rounded-lg border border-slate-200 bg-white py-1 shadow-sm">
                <a href="/perfil" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Mi perfil</a>
                <a href="/perfil/mi-asignacion" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Mi asignación</a>
                <a href="/configuracion" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Configuración</a>
                <hr class="my-1 border-slate-200">
                <form method="POST" action="/logout">
                    @csrf
                    <button class="block w-full px-4 py-2 text-left text-sm text-slate-700 hover:bg-slate-50">Cerrar sesión</button>
                </form>
            </div>
        </div>
    </div>
</header>
