<x-layouts.app>
    @section('title', 'BOM / BOE')

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-medium text-slate-900">BOM / BOE</h2>
            <p class="mt-1 text-sm text-slate-500">Bill of Materials / Equipment del proyecto</p>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <div class="mb-4 flex flex-wrap gap-3">
            <select class="rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none">
                <option>Todos los proyectos</option>
            </select>
            <select class="rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none">
                <option>Todos los tipos</option>
                <option>BOM</option>
                <option>BOE</option>
            </select>
            <select class="rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-gpt-600 focus:outline-none">
                <option>Todos los estados</option>
                <option>En almacén</option>
                <option>Por afilar</option>
                <option>Por fabricar</option>
                <option>Por comprar</option>
                <option>En tránsito</option>
                <option>Entregado</option>
            </select>
        </div>

        <x-empty-state
            title="Sin items en BOM/BOE"
            description="Registra los materiales y equipos necesarios para tus proyectos."
            action-label="Agregar item"
            action-url="#"
        />
    </div>
</x-layouts.app>
