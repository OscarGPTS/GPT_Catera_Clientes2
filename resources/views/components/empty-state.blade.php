@props(['title' => '', 'description' => '', 'actionLabel' => '', 'actionUrl' => '#'])

<div class="flex flex-col items-center justify-center py-12 text-center">
    <svg class="mb-4 h-12 w-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/>
    </svg>
    <h3 class="text-lg font-medium text-slate-900">{{ $title }}</h3>
    <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
    @if($actionLabel)
        <a href="{{ $actionUrl }}" class="mt-4 inline-flex items-center gap-1.5 rounded-md bg-gpt-600 px-4 py-2 text-sm font-medium text-white hover:bg-gpt-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ $actionLabel }}
        </a>
    @endif
</div>
