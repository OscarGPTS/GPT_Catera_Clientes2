<x-layouts.app>
    @section('title', 'Configuración')

    <div class="mb-6">
        <h2 class="text-2xl font-medium text-slate-900">Configuración</h2>
        <p class="mt-1 text-sm text-slate-500">Ajustes del sistema</p>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <h3 class="text-base font-medium text-slate-900">Preferencias del sistema</h3>
        <p class="mt-1 text-sm text-slate-500">Configuración global de la plataforma GPT Services</p>

        <div class="mt-6 space-y-4">
            @php
                $settings = [
                    'minuta_entrega_obligatoria' => 'Minuta de entrega obligatoria (D10)',
                    'bloqueo_cierre_dossier_incompleto' => 'Bloqueo de cierre si dossier incompleto (D11)',
                    'bloqueo_cierre_post_mortem_pendiente' => 'Bloqueo de cierre si post-mortem pendiente',
                    'concentracion_cliente_alerta_umbral' => 'Umbral de alerta de concentración por cliente (%)',
                ];
            @endphp

            @foreach($settings as $key => $label)
                <div class="flex items-center justify-between rounded-md border border-slate-100 bg-slate-50 p-4">
                    <div>
                        <p class="text-sm font-medium text-slate-900">{{ $label }}</p>
                        <p class="text-xs text-slate-500">{{ $key }}</p>
                    </div>
                    <span class="text-sm text-slate-400">Solo lectura</span>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.app>
