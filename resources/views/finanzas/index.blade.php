<x-layouts.app>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-medium text-slate-900">Finanzas</h2>
            <p class="mt-1 text-sm text-slate-500">Gestión financiera y control de cuentas</p>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        {{-- Cuentas --}}
        <div class="group rounded-lg border border-slate-200 bg-white p-6">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gpt-100 text-gpt-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <h3 class="mt-4 text-base font-medium text-slate-900">Cuentas bancarias</h3>
            <p class="mt-1 text-sm text-slate-500">Administrar cuentas bancarias y saldos</p>
            @if($cuentas->count() > 0)
                <div class="mt-3 space-y-1">
                    @foreach($cuentas as $cuenta)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-600">{{ $cuenta->banco ?? '—' }} ••{{ substr($cuenta->numero ?? '—', -4) }}</span>
                            <span class="font-medium text-slate-900">$ {{ number_format($cuenta->saldo ?? 0, 2, '.', ',') }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-3 text-sm text-slate-400">Sin cuentas bancarias registradas</p>
            @endif
        </div>

        {{-- Cierres --}}
        <a href="{{ route('finanzas.cierres') }}" class="group rounded-lg border border-slate-200 bg-white p-6 hover:border-gpt-300 hover:shadow-sm transition-all">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <h3 class="mt-4 text-base font-medium text-slate-900 group-hover:text-blue-600 transition-colors">Cierres mensuales</h3>
            <p class="mt-1 text-sm text-slate-500">Cierres SAT, gerenciales y comparativos</p>
            <div class="mt-4 flex items-center text-sm font-medium text-blue-600">
                <span>Acceder</span>
                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </div>
        </a>

        {{-- Reportes (placeholder for future) --}}
        <div class="group rounded-lg border border-dashed border-slate-300 bg-slate-50 p-6">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-slate-200 text-slate-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            </div>
            <h3 class="mt-4 text-base font-medium text-slate-500">Reportes</h3>
            <p class="mt-1 text-sm text-slate-400">Estados de cuenta y conciliación — próximamente</p>
        </div>
    </div>

    {{-- KPIs financieros --}}
    <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card
            label="Saldo total"
            :value="'$ ' . number_format($saldo_total ?? 0, 2)"
            icon="banknotes"
            color="gpt"
        />
        <x-stat-card
            label="Ingresos del mes"
            :value="'$ ' . number_format($ingresos_mes ?? 0, 2)"
            icon="arrow-trending-up"
            color="success"
        />
        <x-stat-card
            label="Egresos del mes"
            :value="'$ ' . number_format($egresos_mes ?? 0, 2)"
            icon="arrow-trending-down"
            color="danger"
        />
        <x-stat-card
            label="Por conciliar"
            :value="($por_conciliar ?? 0)"
            icon="exclamation-circle"
            :color="($por_conciliar ?? 0) > 0 ? 'warning' : 'success'"
        />
    </div>
</x-layouts.app>
