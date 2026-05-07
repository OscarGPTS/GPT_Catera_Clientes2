<x-layouts.app>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-medium text-slate-900">Socios</h2>
            <p class="mt-1 text-sm text-slate-500">Gestión de accesos para socios y directivos</p>
        </div>
    </x-slot>

    {{-- Override toggle card --}}
    <div class="rounded-lg border border-slate-200 bg-white p-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-medium text-slate-900">Modo socio override</h3>
                <p class="mt-1 text-sm text-slate-500">Permite a usuarios designados por los socios acceder con privilegios extendidos</p>
            </div>
            <form method="POST" action="{{ route('admin.socios.toggle-override') }}">
                @csrf
                <button type="submit" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-gpt-600 focus:ring-offset-2 {{ ($socio_override ?? false) ? 'bg-gpt-600' : 'bg-slate-200' }}">
                    <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ ($socio_override ?? false) ? 'translate-x-6' : 'translate-x-1' }}"></span>
                </button>
            </form>
        </div>
    </div>

    {{-- Lista de emails --}}
    <div class="mt-6 rounded-lg border border-slate-200 bg-white overflow-hidden">
        <div class="border-b border-slate-200 px-6 py-4 flex items-center justify-between">
            <h3 class="text-base font-medium text-slate-900">Correos autorizados</h3>
            <button type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Agregar
            </button>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($socios_emails ?? [] as $email)
                <div class="flex items-center justify-between px-6 py-3 hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-sm font-medium text-slate-600">
                            {{ strtoupper(substr($email->email ?? $email, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900">{{ $email->email ?? $email }}</p>
                            @if($email->nombre ?? $email->name ?? false)
                                <p class="text-xs text-slate-500">{{ $email->nombre ?? $email->name }}</p>
                            @endif
                        </div>
                    </div>
                    <button type="button" class="rounded-lg p-1.5 text-slate-400 hover:text-gpt-red-600 hover:bg-red-50 transition-colors" title="Eliminar">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            @empty
                <div class="px-6 py-10 text-center">
                    <x-empty-state
                        title="Sin correos registrados"
                        description="Agregue los correos electrónicos de los socios que tendrán acceso al sistema."
                        icon="mail"
                    />
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
