@props(['title' => '', 'value' => '', 'subtitle' => '', 'color' => 'gpt'])

@php
$colors = [
    'gpt' => 'from-gpt-50 to-gpt-100 border-gpt-200',
    'blue' => 'from-blue-50 to-blue-100 border-blue-200',
    'green' => 'from-green-50 to-green-100 border-green-200',
    'amber' => 'from-amber-50 to-amber-100 border-amber-200',
    'red' => 'from-gpt-red-50 to-gpt-red-100 border-gpt-red-200',
];
$colorClass = $colors[$color] ?? $colors['gpt'];
@endphp

<div class="rounded-lg border bg-gradient-to-br p-4 {{ $colorClass }}">
    <p class="text-sm font-medium text-slate-600">{{ $title }}</p>
    <p class="mt-2 text-3xl font-semibold text-slate-900">{{ $value }}</p>
    <p class="mt-1 text-xs text-slate-500">{{ $subtitle }}</p>
</div>
