<x-layouts.app>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-medium text-slate-900">Finanzas</h2>
            <p class="mt-1 text-sm text-slate-500">Gestión financiera y control de cuentas</p>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        {{-- Cuentas --}}
        <a href="{{ route('finanzas.cuentas') }}" class="group rounded-lg border border-slate-200 bg-white p-6 hover:border-gpt-300 hover:shadow-sm transition-all">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gpt-100 text-gpt-600 group-hover:bg-gpt-600 group-hover:text-white transition-colors">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <h3 class="mt-4 text-base font-medium text-slate-900 group-hover:text-gpt-600 transition-colors">Cuentas</h3>
            <p class="mt-1 text-sm text-slate-500">Administrar cuentas bancarias y saldos</p>
            <div class="mt-4 flex items-center text-sm font-medium text-gpt-600">
                <span>Acceder</span>
                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </div>
        </a>

        {{-- Estados de cuenta --}}
        <a href="{{ route('finanzas.estados-cuenta') }}" class="group rounded-lg border border-slate-200 bg-white p-6 hover:border-gpt-300 hover:shadow-sm transition-all">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <h3 class="mt-4 text-base font-medium text-slate-900 group-hover:text-blue-600 transition-colors">Estados de cuenta</h3>
            <p class="mt-1 text-sm text-slate-500">Consultar estados de cuenta bancarios</p>
            <div class="mt-4 flex items-center text-sm font-medium text-blue-600">
                <span>Acceder</span>
                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </div>
        </a>

        {{-- Conciliación --}}
        <a href="{{ route('finanzas.conciliacion') }}" class="group rounded-lg border border-slate-200 bg-white p-6 hover:border-gpt-300 hover:shadow-sm transition-all">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
            </div>
            <h3 class="mt-4 text-base font-medium text-slate-900 group-hover:text-emerald-600 transition-colors">Conciliación</h3>
            <p class="mt-1 text-sm text-slate-500">Conciliar movimientos bancarios</p>
            <div class="mt-4 flex items-center text-sm font-medium text-emerald-600">
                <span>Acceder</span>
                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </div>
        </a>
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
