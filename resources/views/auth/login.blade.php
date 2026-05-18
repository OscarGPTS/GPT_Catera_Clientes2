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

    <section class="relative flex w-full shrink-0 items-center justify-center px-8 py-12 lg:w-1/2 lg:py-0 overflow-hidden"
         style="background-image: url('{{ asset('img/bg1.png') }}'); background-size: cover; background-position: center;">
        <div class="absolute inset-0 bg-black/55"></div>
        <div class="relative z-10 mx-auto w-full max-w-sm text-center lg:text-left">

            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-white/50 mb-3">GPT Services</p>
            <h1 class="text-5xl font-bold leading-tight text-white">Cartera<br>de Clientes</h1>
            <div class="mt-4 h-0.5 w-12 bg-white/30 lg:mx-0 mx-auto"></div>
            <p class="mt-4 text-sm text-white/60">Tech Energy Control S.A. de C.V.</p>

            {{-- <div class="mt-8 flex justify-center gap-2 lg:justify-start">
                <span class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-medium text-white/70">ISO 9001</span>
                <span class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-medium text-white/70">ISO 14001</span>
                <span class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-medium text-white/70">ISO 45001</span>
            </div> --}}
        </div>
    </section>

    <section class="flex w-full items-center justify-center bg-white px-4 py-10 lg:w-1/2 lg:py-0">
        <div class="w-full max-w-md">

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-gpt-red-200 bg-gpt-red-50 p-4 text-sm text-gpt-red-800">
                    <p class="font-medium">{{ $errors->first() }}</p>
                </div>
            @endif

            @if(session('success'))
                <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800">
                    <p class="font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <div class="flex flex-col items-center mb-6">
                <img src="{{ asset('img/logo_gpt.svg') }}" alt="GPT Services" class="h-16 w-auto mb-4">
                <p class="text-center text-sm text-slate-500">Inicia sesión con tu cuenta corporativa para acceder a la plataforma</p>
            </div>

            {{-- Email/password login deshabilitado temporalmente --}}
            {{--
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
            --}}

            <div class="mt-6">
                <a href="/auth/google" class="flex w-full items-center justify-center gap-3 rounded-lg border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                    <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    Continuar con Google
                </a>
            </div>

            <p class="mt-8 text-center text-xs text-slate-400">
                ¿Problemas para acceder?
                <a href="mailto:sistemas@gptservices.com" class="font-medium text-slate-600 hover:text-slate-900">sistemas@gptservices.com </a>
            </p>
        </div>
    </section>

</body>
</html>