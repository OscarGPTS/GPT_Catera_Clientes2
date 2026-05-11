<div>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-medium text-slate-900">Listado de Suministros</h2>
            <p class="mt-1 text-sm text-slate-500">FO-GPT-PYT-01-C · Seguimiento de suministros por proyecto</p>
        </div>
    </x-slot>

    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <div class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="proyectoId" class="block text-sm font-medium text-slate-700">Proyecto</label>
                <select id="proyectoId" wire:model.live="proyectoId"
                        class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm focus:border-gpt-600 focus:ring-gpt-600">
                    <option value="">Seleccionar proyecto...</option>
                    @foreach($this->proyectos as $p)
                        <option value="{{ $p->id }}">{{ $p->cp_numero }} — {{ $p->cliente->razon_social ?? 'Sin cliente' }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if($proyectoId && $listado)
        <div class="mt-6 rounded-lg border border-slate-200 bg-white overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Descripción</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Cant.</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Unidad</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Req.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @php
                            $statusLabels = [
                                'en_almacen' => 'En almacén', 'por_afilar' => 'Por afilar',
                                'por_fabricar' => 'Por fabricar', 'por_comprar' => 'Por comprar',
                                'en_transito' => 'En tránsito', 'entregado' => 'Entregado',
                            ];
                            $statusColors = [
                                'en_almacen' => 'bg-green-100 text-green-800', 'por_afilar' => 'bg-amber-100 text-amber-800',
                                'por_fabricar' => 'bg-blue-100 text-blue-800', 'por_comprar' => 'bg-gpt-100 text-gpt-800',
                                'en_transito' => 'bg-amber-100 text-amber-800', 'entregado' => 'bg-slate-100 text-slate-700',
                            ];
                        @endphp
                        @forelse($listado as $item)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $item->tipo === 'BOM' ? 'bg-blue-100 text-blue-800' : 'bg-gpt-100 text-gpt-800' }}">{{ $item->tipo }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-900">{{ $item->descripcion }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm font-medium text-slate-900">{{ number_format($item->cantidad, 2) }}</td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $item->unidad }}</td>
                                <td class="whitespace-nowrap px-4 py-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$item->status] ?? 'bg-slate-100 text-slate-700' }}">{{ $statusLabels[$item->status] ?? $item->status }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500">{{ $item->fecha_requerida?->format('d/m/Y') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center">
                                    <x-empty-state
                                        title="Sin suministros"
                                        description="Este proyecto no tiene items en el BOM/BOE. Agrega materiales desde la sección de BOM/BOE."
                                    />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $listado->links() }}
            </div>
        </div>
    @else
        <div class="mt-6 rounded-lg border border-slate-200 bg-white p-6">
            <x-empty-state
                title="Selecciona un proyecto"
                description="Elige un proyecto en ejecución para ver su listado de suministros."
                action-label="Ir a BOM/BOE"
                action-url="{{ route('proyectos.bom-boe') }}"
            />
        </div>
    @endif
</div>