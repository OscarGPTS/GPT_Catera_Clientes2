# Fase 1 — Bugs Críticos y Seguridad del Módulo de Chat

## Resumen

Corrección de vulnerabilidades de seguridad, aplicación de policies, índices faltantes y configuración de broadcasting.

---

## Tarea 1.1 — Cambiar `Channel` → `PrivateChannel` en eventos de broadcasting

**Problema:** Los 3 eventos de broadcasting usan `Channel()` público, permitiendo que cualquier usuario autenticado escuche cualquier canal sin autorización. `routes/channels.php` define autorización para canales privados pero los eventos no la respetan.

### Archivos modificados:

- `app/Events/ChatMessageSent.php` — `Channel` → `PrivateChannel`
- `app/Events/ChatTyping.php` — `Channel` → `PrivateChannel`
- `app/Events/ChatMessageRead.php` — `Channel` → `PrivateChannel`
- `resources/js/chat.js` — `Echo.channel()` → `Echo.private()`

---

## Tarea 1.2 — Registrar Policies en AppServiceProvider

**Problema:** `ChatCanalPolicy` y `ChatMensajePolicy` existen pero nunca se registran explícitamente. Si el auto-discovery falla por el namespace `App\Models\Chat\`, las policies no se aplican.

### Archivo modificado:

- `app/Providers/AppServiceProvider.php` — Agregar `Gate::policy()` para ambos modelos

---

## Tarea 1.3 — Aplicar autorización en componentes Livewire

**Problema:** Ningún componente Livewire verifica que el usuario tenga permiso para ver un canal o enviar mensajes. Un usuario que conozca un `channelId` puede acceder a cualquier canal.

### Archivos modificados:

- `app/Livewire/Chat/ChatDrawer.php`:
  - `selectChannel()` — Agregar `Gate::allows('view', $canal)`, redirigir si no autorizado
  - `sendMessage()` — Verificar membership antes de enviar

- `app/Livewire/Chat/AdminChat.php`:
  - `deleteChannel()` — Agregar `Gate::allows('delete', $canal)`
  - `saveChannel()` — Agregar `Gate::allows('update', $canal)` si es edición

---

## Tarea 1.4 — Crear migration para índices faltantes

**Problema:** La migración original no tiene índices compuestos ni únicos más allá de PKs y FKs. Esto causa:
- Duplicados en membresías (`chat_canal_miembros`): un usuario puede agregarse múltiples veces al mismo canal
- Duplicados en lecturas (`chat_lecturas`): múltiples registros de lectura para misma pareja canal+usuario
- Queries lentos al buscar mensajes por canal y fecha, o menciones no leídas por usuario

### Nuevo archivo:

- `database/migrations/2026_05_16_000000_add_indexes_and_unique_constraints_to_chat_tables.php`

### Índices a crear:

| Tabla | Índice | Tipo |
|-------|--------|------|
| `chat_canal_miembros` | `(canal_id, user_id)` | UNIQUE |
| `chat_lecturas` | `(canal_id, user_id)` | UNIQUE |
| `chat_mensajes` | `(canal_id, created_at)` | INDEX |
| `chat_mensajes` | `(user_id)` | INDEX |
| `chat_menciones` | `(user_id, leido_at)` | INDEX |

---

## Tarea 1.5 — Verificar broadcasting en `.env`

**Estado:** ✅ Ya verificado. `BROADCAST_CONNECTION=reverb` ya está configurado en `.env` (línea 36). Las credenciales de Reverb están duplicadas (local + producción) pero funcional para desarrollo local.

---

## Archivos afectados (total: 8)

| # | Archivo | Acción |
|---|---------|--------|
| 1 | `app/Events/ChatMessageSent.php` | Modificar |
| 2 | `app/Events/ChatTyping.php` | Modificar |
| 3 | `app/Events/ChatMessageRead.php` | Modificar |
| 4 | `resources/js/chat.js` | Modificar |
| 5 | `app/Providers/AppServiceProvider.php` | Modificar |
| 6 | `app/Livewire/Chat/ChatDrawer.php` | Modificar |
| 7 | `app/Livewire/Chat/AdminChat.php` | Modificar |
| 8 | `database/migrations/2026_05_16_000000_add_indexes_and_unique_constraints_to_chat_tables.php` | Nuevo |

---

## Orden de ejecución

1. Tarea 1.1 (PrivateChannel) — sin esto, la autorización de canales nunca funciona
2. Tarea 1.2 (Policies) — registrar para que Gate::allows funcione
3. Tarea 1.3 (Autorización) — aplicar las checks en Livewire
4. Tarea 1.4 (Migration) — adicionar índices y constraints
5. Tarea 1.5 (ENV) — ya verificado