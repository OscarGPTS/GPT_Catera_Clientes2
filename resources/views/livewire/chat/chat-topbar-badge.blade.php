<a
    href="/chat"
    wire:poll.10s="loadUnread"
    class="relative rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-600"
>
    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
    </svg>
    @if($unreadCount > 0)
    <span class="absolute right-1 top-1 flex h-4 min-w-[16px] items-center justify-center rounded-full bg-gpt-600 px-1 text-[10px] font-semibold text-white">
        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
    </span>
    @endif
</a>
