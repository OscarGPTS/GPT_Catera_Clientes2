<div wire:poll.10s="loadCanales">
    {{-- Chat button --}}
    <div class="mb-1 px-1">
        <a
            href="/chat"
            class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-colors text-slate-300 hover:bg-slate-800/60 hover:text-slate-100"
            :class="{ 'hidden!': !sidebarOpen && !sidebarMobileOpen }"
        >
            <svg class="h-4 w-4 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            <span class="text-[12px]">Chat</span>
        </a>
    </div>

    {{-- Channel list --}}
    <div class="space-y-0.5">
        @foreach($canales as $canal)
        <a
            href="/chat?canal={{ $canal['id'] }}"
            class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left transition-colors text-slate-300 hover:bg-slate-800/60 hover:text-slate-100 group"
            :class="{ 'hidden!': !sidebarOpen && !sidebarMobileOpen }"
        >
            @if($canal['tipo'] === 'privado')
                <svg class="h-4 w-4 shrink-0 text-slate-500 group-hover:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            @elseif($canal['tipo'] === 'proyecto')
                <svg class="h-4 w-4 shrink-0 text-slate-500 group-hover:text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
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
        </a>
        @endforeach
    </div>

    @if(empty($canales))
    <p
        class="px-2.5 py-1.5 text-[11px] text-slate-600 italic"
        :class="{ 'hidden!': !sidebarOpen && !sidebarMobileOpen }"
    >Sin canales activos</p>
    @endif
</div>