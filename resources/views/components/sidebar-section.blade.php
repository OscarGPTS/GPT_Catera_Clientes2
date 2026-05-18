@props(['label', 'collapsible' => false])

@php
$sectionId = 'section-' . \Illuminate\Support\Str::slug($label);
@endphp

<div class="mb-1" x-data="{ open: true }">
    <button
        @if($collapsible) @@click="open = !open" @endif
        class="flex w-full items-center gap-2 px-4 py-2 text-left"
        :class="{ 'cursor-pointer hover:bg-slate-800/30': {{ $collapsible ? 'true' : 'false' }}, 'cursor-default': {{ $collapsible ? 'false' : 'true' }} }"
    >
        <p
            class="truncate text-[11px] font-semibold uppercase tracking-[0.15em] text-slate-500 select-none"
            :class="{ 'hidden': !sidebarOpen && !sidebarMobileOpen, 'block': sidebarOpen || sidebarMobileOpen }"
        >{{ $label }}</p>
        @if($collapsible)
            <svg
                class="h-3 w-3 shrink-0 text-slate-600 transition-transform duration-200"
                :class="{ 'rotate-90': open, 'hidden': !sidebarOpen && !sidebarMobileOpen }"
                fill="none" stroke="currentColor" viewBox="0 0 24 24"
            ><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        @endif
    </button>

    {{-- Collapsed state: show a subtle divider instead of label --}}
    <div
        class="mx-4 my-2 h-px bg-gradient-to-r from-transparent via-slate-700/50 to-transparent"
        :class="{ 'hidden': sidebarOpen  || sidebarMobileOpen, 'block': !sidebarOpen  && !sidebarMobileOpen }"
    ></div>

    <div
        class="space-y-0.5 px-2" style="margin-top:-12px;"
        @if($collapsible) x-show="open" x-collapse @endif
    >
        {{ $slot }}
    </div>
</div>
