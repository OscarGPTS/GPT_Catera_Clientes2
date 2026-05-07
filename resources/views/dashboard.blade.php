<x-layouts.app>
@section('title', 'Dashboard')

    {{-- KPI Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card title="Pipeline activo" value="18" subtitle="Oportunidades en curso" color="blue" />
        <x-stat-card title="Adjudicado YTD" value="3" subtitle="Proyectos adjudicados" color="green" />
        <x-stat-card title="En ejecución" value="5" subtitle="Proyectos en obra" color="gpt" />
        <x-stat-card title="Cartera comprometida" value="$2.4M" subtitle="Monto próximo mes" color="amber" />
    </div>

    {{-- Recent opportunities --}}
    <div class="rounded-lg border border-slate-200 bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
            <h2 class="text-lg font-medium text-slate-900">Oportunidades recientes</h2>
            <a href="/oportunidades" class="text-sm font-medium text-gpt-600 hover:text-gpt-700">Ver todas</a>
        </div>
        <div class="p-6">
            @php
                $proyectos = App\Models\Proyectos\Proyecto::with(['cliente', 'sublinea'])->latest()->take(5)->get();
            @endphp
            @if($proyectos->isEmpty())
                <x-empty-state
                    title="Sin oportunidades"
                    description="Crea tu primera oportunidad para empezar."
                    action-label="Nueva oportunidad"
                    action-url="/oportunidades/nueva"
                />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50 text-xs font-medium uppercase tracking-wider text-slate-600">
                                <th class="px-4 py-3 text-left">CP</th>
                                <th class="px-4 py-3 text-left">Cliente</th>
                                <th class="px-4 py-3 text-left">Sublinea</th>
                                <th class="px-4 py-3 text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($proyectos as $proyecto)
                                <tr class="border-b border-slate-200 text-sm hover:bg-slate-50">
                                    <td class="px-4 py-3 font-medium text-slate-900">{{ $proyecto->cp_numero ?? '—' }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $proyecto->cliente?->razon_social ?? '—' }}</td>
                                    <td class="px-4 py-3 text-slate-700">{{ $proyecto->sublinea?->nombre ?? '—' }}</td>
                                    <td class="px-4 py-3 ">
                                        <x-badge :label="$proyecto->estado" :status="$proyecto->estado" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
