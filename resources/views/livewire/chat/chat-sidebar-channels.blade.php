<div>
    @foreach($canales as $canal)
    <button
        wire:click="openChannel({{ $canal['id'] }})"
        class="flex w-full items-center gap-2 rounded-md px-3 py-1.5 text-left text-[12px] transition-colors text-slate-300 hover:bg-slate-800/50 hover:text-slate-200"
        :class="{ 'hidden!': !sidebarOpen && !sidebarMobileOpen }"
    >
        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded bg-slate-700 text-[8px] font-semibold text-slate-400">
            {{ strtoupper(substr($canal['nombre'], 0, 2)) }}
        </span>
        <span class="min-w-0 flex-1">
            <span class="block truncate">{{ $canal['nombre'] }}</span>
            @if($canal['ultimo_mensaje'])
            <span class="block truncate text-[10px] text-slate-500">{{ $canal['ultimo_mensaje'] }}</span>
            @endif
        </span>
        @if($canal['no_leidos'] > 0)
        <span class="flex h-4 min-w-[16px] shrink-0 items-center justify-center rounded-full bg-gpt-600 px-1 text-[9px] font-semibold text-white">
            {{ $canal['no_leidos'] > 99 ? '99+' : $canal['no_leidos'] }}
        </span>
        @endif
    </button>
    @endforeach

    @if(count($canales) > 0)
    <a
        href="/chat"
        class="flex items-center gap-2 rounded-md px-3 py-1.5 text-[11px] text-slate-500 hover:text-slate-300 transition-colors"
        :class="{ 'hidden!': !sidebarOpen && !sidebarMobileOpen }"
    >
        <span class="flex h-5 w-5 shrink-0 items-center justify-center">...</span>
        <span>Ver todos los canales</span>
    </a>
    @endif

    @if(empty($canales))
    <p
        class="px-3 py-1.5 text-[11px] text-slate-600 italic"
        :class="{ 'hidden!': !sidebarOpen && !sidebarMobileOpen }"
    >Sin canales activos</p>
    @endif
</div>
