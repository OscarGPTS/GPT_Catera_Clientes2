<x-layouts.app>
    @section('title', 'Vincular método de autenticación')
    <div class="mx-auto max-w-2xl">
        <h2 class="text-2xl font-medium text-slate-900">Métodos de autenticación</h2>
        <p class="mt-1 text-sm text-slate-500">Gestiona los métodos con los que puedes acceder a tu cuenta.</p>

        @if(session('success'))
            <div class="mt-4 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mt-4 rounded-lg border border-gpt-red-200 bg-gpt-red-50 p-4 text-sm text-gpt-red-800">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="mt-6 space-y-4">
            @foreach($providers as $provider)
                <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-white p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100">
                            @if($provider->provider === 'email_password')
                                <svg class="h-5 w-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            @elseif($provider->provider === 'google')
                                <svg class="h-5 w-5 text-slate-600" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                            @elseif($provider->provider === 'microsoft')
                                <svg class="h-5 w-5 text-slate-600" viewBox="0 0 24 24"><rect x="1" y="1" width="9" height="9" fill="#F25022"/><rect x="1" y="12" width="9" height="9" fill="#7FBA00"/><rect x="12" y="1" width="9" height="9" fill="#00A4EF"/><rect x="12" y="12" width="9" height="9" fill="#FFB900"/></svg>
                            @else
                                <svg class="h-5 w-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-2.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium text-slate-900">
                                @if($provider->provider === 'email_password') Email y contraseña
                                @elseif($provider->provider === 'google') Google
                                @elseif($provider->provider === 'microsoft') Microsoft
                                @elseif($provider->provider === 'apple') Apple
                                @elseif($provider->provider === 'auth0') Auth0 corporativo
                                @else {{ ucfirst($provider->provider) }}
                                @endif
                            </p>
                            <p class="text-xs text-slate-500">
                                Vinculado el {{ $provider->linked_at->format('d/m/Y') }}
                                @if($provider->last_used_at) · Último uso {{ $provider->last_used_at->format('d/m/Y') }} @endif
                                @if($provider->is_primary) · <span class="font-medium text-gpt-600">Principal</span>@endif
                            </p>
                        </div>
                    </div>
                    @if(!$provider->is_primary)
                        <form method="POST" action="{{ route('auth.link-provider.unlink', $provider->provider) }}" class="inline">
                            @csrf @method('DELETE')
                            <x-button variant="ghost" type="submit" class="text-gpt-red-600 hover:text-gpt-red-700">Desvincular</x-button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            <h3 class="text-lg font-medium text-slate-900">Vincular otro método</h3>
            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-3">
                <a href="{{ route('auth.link-provider.redirect', 'google') }}" class="flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Google
                </a>
                <a href="{{ route('auth.link-provider.redirect', 'microsoft') }}" class="flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <svg class="h-4 w-4" viewBox="0 0 24 24"><rect x="1" y="1" width="9" height="9" fill="#F25022"/><rect x="1" y="12" width="9" height="9" fill="#7FBA00"/><rect x="12" y="1" width="9" height="9" fill="#00A4EF"/><rect x="12" y="12" width="9" height="9" fill="#FFB900"/></svg>
                    Microsoft
                </a>
                <a href="{{ route('auth.link-provider.redirect', 'apple') }}" class="flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                    Apple
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>