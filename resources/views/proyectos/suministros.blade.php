<x-layouts.app>
    @section('title', 'Listado de Suministros')

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-medium text-slate-900">Listado de Suministros</h2>
            <p class="mt-1 text-sm text-slate-500">FO-GPT-PYT-01-C · Seguimiento de suministros por proyecto</p>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <div class="mb-4 flex flex-wrap gap-3">
            <select class="rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none">
                <option>Todos los proyectos</option>
            </select>
            <button class="rounded-md border border-slate-200 bg-white px-4 py-2 text-sm text-slate-600 hover:bg-slate-50">
                Exportar Excel
            </button>
        </div>

        <x-empty-state
            title="Sin listados de suministros"
            description="Los suministros se generan a partir del BOM/BOE del proyecto."
            action-label="Ir a BOM/BOE"
            action-url="/proyectos/bom-boe"
        />
    </div>
</x-layouts.app>
