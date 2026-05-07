<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GPT Services')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-slate-50 font-sans text-slate-900 antialiased" x-data="{ sidebarOpen: true, sidebarMobileOpen: false }">
    <x-topbar />

    <div class="flex h-[calc(100vh-64px)] overflow-hidden" x-on:resize.window="sidebarOpen = window.innerWidth >= 1024">
        <x-sidebar />

        <main class="flex-1 overflow-y-auto bg-slate-50 p-4 lg:p-6">
            <div class="mx-auto max-w-7xl">
                {{ $slot }}
            </div>
        </main>
    </div>

    @livewireScripts
    <script>
        document.addEventListener('livewire:navigated', function() {
            if (window.innerWidth < 1024) {
                var el = document.querySelector('[x-data]');
                if (el && el.__x) el.__x.$data.sidebarMobileOpen = false;
            }
        });
        var stored = localStorage.getItem('sidebar_open');
        if (stored !== null) {
            var el = document.querySelector('[x-data]');
            if (el && el.__x) el.__x.$data.sidebarOpen = stored === 'true';
        }
    </script>
</body>
</html>
