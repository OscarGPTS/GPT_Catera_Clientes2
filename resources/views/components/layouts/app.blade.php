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
<body class="h-full bg-slate-50 font-sans text-slate-900 antialiased" x-data="{
        sidebarOpen: (localStorage.getItem('sidebar_open') === null ? true : localStorage.getItem('sidebar_open') === 'true'),
        sidebarMobileOpen: false
    }"
    x-on:resize.window.debounce.150ms="if (window.innerWidth >= 1024 && sidebarMobileOpen) { sidebarMobileOpen = false }"
>
    <div class="flex h-screen flex-col overflow-hidden">
        <x-topbar />

        <div class="relative flex flex-1 overflow-hidden">
            <x-sidebar />

            <main class="flex-1 overflow-hidden {{ $fullWidth ?? false ? '' : 'overflow-y-auto bg-slate-50 p-3 sm:p-4 lg:p-6 xl:p-8' }}">
                @if($fullWidth ?? false)
                    {{ $slot }}
                @else
                    <div class="w-full">
                        @if(!empty($header))
                            <div class="mb-4 sm:mb-6 pb-3 sm:pb-4 border-b border-slate-200">
                                {{ $header }}
                            </div>
                        @endif
                        {{ $slot }}
                    </div>
                @endif
            </main>
        </div>
    </div>

    @livewireScriptConfig
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <livewire:chat.chat-drawer />
    <script>
        document.addEventListener('livewire:navigated', function() {
            if (window.innerWidth < 1024) {
                var el = document.querySelector('body[x-data]');
                if (el && el._x_dataStack) el._x_dataStack[0].sidebarMobileOpen = false;
            }
        });
    </script>
</body>
</html>
