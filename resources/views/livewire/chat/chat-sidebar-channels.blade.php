<div>
    <div class="space-y-0.5">
        @foreach($canales as $canal)
        <button
            wire:click="openChannel({{ $canal['id'] }})"
            class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-colors text-slate-300 hover:bg-slate-800/60 hover:text-slate-100 group"
            :class="{ 'hidden!': !sidebarOpen && !sidebarMobileOpen }"
        >
            @if($canal['tipo'] === 'privado')
                <svg class="h-4 w-4 shrink-0 text-slate-500 group-hover:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            @else
                <span class="shrink-0 text-[11px] font-semibold text-slate-500 group-hover:text-slate-300">#</span>
            @endif
            <span class="min-w-0 flex-1">
                <span class="block truncate text-[12px]">{{ $canal['nombre'] }}</span>
                @if($canal['ultimo_mensaje'])
                <span class="block truncate text-[10px] text-slate-500 group-hover:text-slate-400">{{ $canal['ultimo_mensaje'] }}</span>
                @endif
            </span>
            @if($canal['no_leidos'] > 0)
            <span class="flex h-4 min-w-[16px] shrink-0 items-center justify-center rounded-full bg-gpt-600 px-1 text-[9px] font-semibold text-white">
                {{ $canal['no_leidos'] > 99 ? '99+' : $canal['no_leidos'] }}
            </span>
            @endif
        </button>
        @endforeach
    </div>

    @if(count($canales) > 0)
    <a
        href="/chat"
        class="mt-1 flex items-center gap-2 rounded-lg px-2.5 py-1.5 text-[11px] text-slate-500 hover:text-slate-300 hover:bg-slate-800/40 transition-colors"
        :class="{ 'hidden!': !sidebarOpen && !sidebarMobileOpen }"
    >
        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 3.75v4.5m0-4.5h4.5m-4.5 0L9 9M3.75 20.25v-4.5m0 4.5h4.5m-4.5 0L9 15M20.25 3.75h-4.5m4.5 0v4.5m0-4.5L15 9m5.25 11.25h-4.5m4.5 0v-4.5m0 4.5L15 15"/></svg>
        <span>Ver todos los canales</span>
    </a>
    @endif

    @if(empty($canales))
    <p
        class="px-2.5 py-1.5 text-[11px] text-slate-600 italic"
        :class="{ 'hidden!': !sidebarOpen && !sidebarMobileOpen }"
    >Sin canales activos</p>
    @endif
</div>