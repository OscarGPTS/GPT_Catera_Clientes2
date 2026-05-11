<div>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-medium text-slate-900">Configuración</h2>
            <p class="mt-1 text-sm text-slate-500">Ajustes del sistema</p>
        </div>
    </x-slot>

    @if($saved)
        <div x-data="{ show: true }" x-init="setTimeout(() => { show = false }, 3000)" x-show="show" x-transition
             class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4">
            <p class="text-sm font-medium text-green-800">Configuración guardada correctamente.</p>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        {{-- Business rules section --}}
        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <h3 class="text-base font-medium text-slate-900">Reglas de negocio</h3>
            <p class="mt-1 text-sm text-slate-500">Configuración global de la plataforma GPT Services</p>

            <div class="mt-6 space-y-4">
                <label class="flex items-center justify-between rounded-md border border-slate-100 bg-slate-50 p-4 cursor-pointer">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" wire:model="minuta_entrega_obligatoria" class="h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                        <div>
                            <p class="text-sm font-medium text-slate-900">Minuta de entrega obligatoria</p>
                            <p class="text-xs text-slate-500">D10 — Requiere minuta firmada para cerrar proyecto</p>
                        </div>
                    </div>
                </label>

                <label class="flex items-center justify-between rounded-md border border-slate-100 bg-slate-50 p-4 cursor-pointer">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" wire:model="bloqueo_cierre_dossier_incompleto" class="h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                        <div>
                            <p class="text-sm font-medium text-slate-900">Bloqueo de cierre si dossier incompleto</p>
                            <p class="text-xs text-slate-500">D11 — Impide cierre administrativo con libro de proyecto incompleto</p>
                        </div>
                    </div>
                </label>

                <label class="flex items-center justify-between rounded-md border border-slate-100 bg-slate-50 p-4 cursor-pointer">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" wire:model="bloqueo_cierre_post_mortem_pendiente" class="h-4 w-4 rounded border-slate-300 text-gpt-600 focus:ring-gpt-600">
                        <div>
                            <p class="text-sm font-medium text-slate-900">Bloqueo de cierre si post-mortem pendiente</p>
                            <p class="text-xs text-slate-500">Requiere lecciones aprendidas antes de cerrar proyecto</p>
                        </div>
                    </div>
                </label>

                <div class="flex items-center justify-between rounded-md border border-slate-100 bg-slate-50 p-4">
                    <div>
                        <p class="text-sm font-medium text-slate-900">Umbral de alerta de concentración por cliente</p>
                        <p class="text-xs text-slate-500">Porcentaje máximo de participación de un solo cliente en el pipeline</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="number" wire:model="concentracion_cliente_alerta_umbral" min="1" max="100"
                               class="w-20 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-right text-slate-900 focus:border-gpt-600 focus:ring-gpt-600">
                        <span class="text-sm text-slate-500">%</span>
                    </div>
                    @error('concentracion_cliente_alerta_umbral') <p class="mt-1 text-xs text-gpt-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Auth section --}}
        <div class="rounded-lg border border-slate-200 bg-white p-6">
            <h3 class="text-base font-medium text-slate-900">Autenticación</h3>
            <p class="mt-1 text-sm text-slate-500">Dominios corporativos permitidos para login automático</p>

            <div class="mt-6">
                <label for="auth_dominios_corporativos_input" class="block text-sm font-medium text-slate-700 mb-1">Dominios corporativos</label>
                <input type="text" id="auth_dominios_corporativos_input" wire:model="auth_dominios_corporativos_input"
                       class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-gpt-600 focus:ring-gpt-600"
                       placeholder="gpt.com.mx, techenergycontrol.com">
                <p class="mt-1 text-xs text-slate-500">Separar dominios con comas</p>
            </div>
        </div>

        {{-- Save button --}}
        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-gpt-600 px-6 py-2 text-sm font-medium text-white shadow-sm hover:bg-gpt-700 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                Guardar configuración
            </button>
        </div>
    </form>
</div>