<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión — GPT Services</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col-reverse lg:flex-row font-sans">

    <section class="flex w-full shrink-0 items-center justify-center bg-slate-900 px-8 py-12 lg:w-1/2 lg:py-0">
        <div class="mx-auto w-full max-w-sm text-center lg:text-left">
            <h1 class="text-4xl font-semibold text-white">GPT Services</h1>
            <p class="mt-3 text-sm text-slate-400">Plataforma de gestión de proyectos</p>
            <p class="mt-1 text-xs text-slate-500">Tech Energy Control S.A. de C.V.</p>

            <div class="mt-8 flex justify-center gap-2 lg:justify-start">
                <span class="inline-flex items-center rounded-full border border-slate-700 bg-slate-800 px-3 py-1 text-xs font-medium text-slate-400">ISO 9001</span>
                <span class="inline-flex items-center rounded-full border border-slate-700 bg-slate-800 px-3 py-1 text-xs font-medium text-slate-400">ISO 14001</span>
                <span class="inline-flex items-center rounded-full border border-slate-700 bg-slate-800 px-3 py-1 text-xs font-medium text-slate-400">ISO 45001</span>
            </div>
        </div>
    </section>

    <section class="flex w-full items-center justify-center bg-white px-4 py-10 lg:w-1/2 lg:py-0">
        <div class="w-full max-w-md">

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-gpt-red-200 bg-gpt-red-50 p-4 text-sm text-gpt-red-800">
                    <p class="font-medium">{{ $errors->first() }}</p>
                </div>
            @endif

            <button type="button" class="flex w-full items-center gap-3 rounded-lg bg-gpt-600 px-5 py-3 text-left text-white hover:bg-gpt-700 transition-colors">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                </svg>
                <div>
                    <div class="text-sm font-semibold">Continuar con cuenta corporativa</div>
                    <div class="text-xs text-gpt-200">@gptservices.com · @satechenergy.com</div>
                </div>
            </button>

            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                <div class="relative flex justify-center text-xs"><span class="bg-white px-3 text-slate-400">o</span></div>
            </div>

            <form method="POST" action="{{ route('login.authenticate') }}">
                @csrf
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-slate-700">Correo electrónico</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200">
                </div>

                <div class="mb-6">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-slate-700">Contraseña</label>
                        <a href="#" class="text-xs font-medium text-gpt-600 hover:text-gpt-700">¿Olvidaste tu contraseña?</a>
                    </div>
                    <input type="password" id="password" name="password" required
                        class="mt-1 block w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-gpt-600 focus:outline-none focus:ring-2 focus:ring-gpt-200">
                </div>

                <button type="submit" class="w-full rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-900 transition-colors">
                    Iniciar sesión
                </button>
            </form>

            <div class="mt-6 grid grid-cols-3 gap-3">
                <button type="button" class="flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                    <svg class="h-4 w-4" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    <span class="hidden sm:inline">Google</span>
                </button>
                <button type="button" class="flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                    <svg class="h-4 w-4" viewBox="0 0 24 24"><rect x="1" y="1" width="9" height="9" fill="#F25022"/><rect x="1" y="12" width="9" height="9" fill="#7FBA00"/><rect x="12" y="1" width="9" height="9" fill="#00A4EF"/><rect x="12" y="12" width="9" height="9" fill="#FFB900"/></svg>
                    <span class="hidden sm:inline">Microsoft</span>
                </button>
                <button type="button" class="flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
                    <span class="hidden sm:inline">Apple</span>
                </button>
            </div>

            <p class="mt-8 text-center text-xs text-slate-400">
                ¿Problemas para acceder?
                <a href="mailto:soporte@gptservices.com" class="font-medium text-slate-600 hover:text-slate-900">soporte@gptservices.com</a>
            </p>
        </div>
    </section>

</body>
</html>
