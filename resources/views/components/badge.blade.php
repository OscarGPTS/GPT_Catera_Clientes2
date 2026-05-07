@props(['label' => '', 'status' => ''])

@php
$classes = match($status) {
    'cotizando' => 'bg-blue-100 text-blue-800',
    'cotizado' => 'bg-gpt-100 text-gpt-800',
    'presentado' => 'bg-gpt-100 text-gpt-800',
    'adjudicado_pendiente' => 'bg-amber-100 text-amber-800',
    'adjudicado_firmado' => 'bg-green-100 text-green-800',
    'en_ejecucion' => 'bg-green-100 text-green-800',
    'en_cierre' => 'bg-gpt-100 text-gpt-800',
    'cerrado' => 'bg-slate-100 text-slate-700',
    'cancelado' => 'bg-slate-100 text-slate-500',
    'perdido' => 'bg-gpt-red-100 text-gpt-red-800',
    'archivado' => 'bg-slate-100 text-slate-500',
    'en_revision' => 'bg-amber-100 text-amber-800',
    default => 'bg-slate-100 text-slate-700',
};

$labelText = match($status) {
    'en_revision' => 'En revisión',
    'cotizando' => 'Cotizando',
    'cotizado' => 'Cotizado',
    'presentado' => 'Presentado',
    'adjudicado_pendiente' => 'Adjudicado pend.',
    'adjudicado_firmado' => 'Adjudicado',
    'en_ejecucion' => 'En ejecución',
    'en_cierre' => 'En cierre',
    'cerrado' => 'Cerrado',
    'cancelado' => 'Cancelado',
    'perdido' => 'Perdido',
    'archivado' => 'Archivado',
    default => $label,
};
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $classes }}">
    {{ $labelText }}
</span>
