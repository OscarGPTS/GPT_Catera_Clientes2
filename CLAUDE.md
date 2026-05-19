# Project notes

Laravel 12 + Livewire 3 + Alpine + Tailwind v4 + Vite. App: cartera de clientes / pipeline comercial (GPT Services).

Desarrollo local: **Windows / XAMPP**. Producción: **Linux**. Las diferencias entre sistemas operativos son la fuente más común de bugs que aparecen *solo en el servidor*.

## Convenciones obligatorias por portabilidad cross-OS

### 1. Nombres de vista — siempre con puntos

Laravel resuelve `view('a.b.c')` → `resources/views/a/b/c.blade.php` reemplazando los puntos por el separador del SO. Si pones backslash literal en la cadena, en Linux ese `\` queda como carácter del nombre del archivo y revienta con `View not found`.

```php
// NO
return view('livewire\chat\chat-index');
return view('livewire/chat/chat-index');

// SÍ
return view('livewire.chat.chat-index');
```

Aplica también a `@include`, `@extends`, `@component`, `View::make()`, etc. Solo puntos, nunca slashes.

### 2. Nombres de archivo blade — lowercase con guiones

```
chat-index.blade.php       ✅
oportunidades-index.blade.php  ✅
ChatIndex.blade.php        ❌
chatIndex.blade.php        ❌
```

Linux distingue mayúsculas/minúsculas en filenames; Windows no. Una referencia con casing distinto al archivo funciona en dev y rompe en prod.

### 3. Componentes Blade — kebab-case con minúscula inicial

```html
<x-sidebar-section />   ✅
<x-Sidebar-Section />   ❌
```

### 4. Namespaces PHP — respeta el casing del archivo

PSR-4 es case-sensitive en Linux. Si el archivo se llama `ChatIndex.php`, el `use` debe ser `App\Livewire\Chat\ChatIndex` (mismo casing), no `App\livewire\chat\ChatIndex`.

## Despliegue al servidor

Comandos en orden cuando algo se ve raro tras un deploy:

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan view:clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
composer dump-autoload
php artisan optimize     # esto precompila vistas; falla si hay una vista con backslash o casing erróneo
```

Si `optimize` falla en una vista, NO bypasses con `view:clear`. Eso solo posterga el problema. Arregla el path de la vista en el código y vuelve a correr `optimize`.

## Frontend / Vite

- `public/hot` existe **solo** cuando `npm run dev` está corriendo. Si queda huérfano (Ctrl+C limpio lo borra; `kill -9` no), Laravel intenta servir assets desde `http://localhost:5173` y todo el JS muere con 404. Si nada de JS funciona en local, verifica que `public/hot` no esté presente.
- Producción solo usa `npm run build` → assets compilados en `public/build/assets/`.
- Después de `npm run build`, el navegador necesita **Ctrl+F5** para bypass del cache.

## Realtime / Broadcasting

**No usar Pusher/Reverb en este proyecto.** Se removió laravel-echo y pusher-js. La estrategia de "casi-realtime" es Livewire `wire:poll`:

- Chat drawer abierto: `wire:poll.3s="loadMensajes"` (gated por `$open`)
- Chat sidebar (badges): `wire:poll.10s="loadCanales"`
- Notifications dropdown: `wire:poll.10s="loadNotifications"`
- /chat (ChatIndex): `wire:poll.3s="loadMensajes"` (gated por `$activeChannelId`)

Los Event classes con `implements ShouldBroadcast` quedaron en el código pero con `BROADCAST_CONNECTION=log` son no-op. Si en el futuro se necesita realtime de baja latencia (typing indicator, presencia), considera reactivar Reverb solo para esa feature, no para todo.

## Auth

- Login vía Auth0/Google: [`Auth0Controller`](app/Http/Controllers/Auth/Auth0Controller.php).
- Login email/password: [`LoginController`](app/Http/Controllers/Auth/LoginController.php).
- **La validación contra el servicio RH externo está temporalmente comentada** en `provisionCorporateUser()` ([AuthOrchestrator.php](app/Services/Auth/AuthOrchestrator.php)) y en `LoginController::authenticate()`. Reactivar cuando el servicio RH esté estabilizado — el código está preservado en comentarios marcados con `=== Validación RH deshabilitada temporalmente ===`.

## Dependencias clave

- **Livewire 3** (no 2): public methods son callables remotamente sin necesidad de `$listeners`. Usa `#[On('evento')]` para listeners explícitos.
- **Alpine 3** vía livewire.esm.js: importado desde el bundle de Livewire, no instalado aparte.
- **Tailwind v4** vía `@tailwindcss/vite`: config en `resources/css/app.css` con `@theme` y `@source`, no en `tailwind.config.js`.
