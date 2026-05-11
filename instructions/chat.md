Plan Final — Módulo 10: Chat + Notificaciones
Arquitectura decidida:
- Sidebar: lista inline de canales del usuario (tipo Slack)
- Chat: drawer 480px (rápido) + página /chat (completa)
- Notificaciones: clic en mención → abre drawer en ese canal
Orden de ejecución (9 fases):
Fase	Entregables
F1 Dependencias	Reverb + Echo + Pusher instalados. Migración notifications. broadcasting.php + channels.php + .env + bootstrap.js configurados.
F2 Modelos	5 modelos en App\Models\Chat\. Relaciones en User.php. 2 Policies (ChatCanalPolicy, ChatMensajePolicy).
F3 Notificaciones	8 clases Notification nuevas. NotificacionesDropdown (Livewire, topbar badge + dropdown). NotificacionesIndex (Livewire, página /notificaciones). Topbar integrado. Ruta /notificaciones.
F4 Chat UI	Sección "Chat" en sidebar con canales inline. ChatDrawer (Livewire, página /chat). ChatSidebar + ChatChannel + ChatMessage + ChatInput (Livewire). Drawer 480px overlay global. Topbar integrado. Ruta /chat.
F5 Tiempo real	Eventos broadcast (ChatMessageSent, ChatMessageRead, ChatTyping). Canales privados en channels.php. Echo sincronización. Badge dinámico. Typing indicators.
F6 Auto-creación	Observer al crear proyecto → canal proyecto-{cp} con miembros. Seeder/command canales departamentales + dirección. Creación de privados.
F7 Admin	Admin ve todos los canales (bypass Policy). Ruta /admin/chat con dashboard gestión. Search global de mensajes.
F8 Tests	ChatDemoSeeder (3 canales, 30 mensajes). Tests unitarios + feature + policies.
F9 Polish	Mobile full-screen. Search con highlight. Skeleton loading. Adjuntos (drag&drop imágenes).
Diseño UI (siguiendo DESIGN_SYSTEM.md):
- Sidebar items: fondo slate-900, activo bg-slate-800 + barra izquierda gpt-600
- Drawer: fondo white, cabecera con borde slate-200
- Mensajes propios: alineados derecha, burbuja gpt-500
- Mensajes otros: alineados izquierda, burbuja slate-100
- Menciones @usuario: resaltadas en gpt-600 font-medium
- Input: borde slate-200, focus gpt-600
- Badge no leídos: gpt-600 (naranja), igual que la campana actual
- Tipografía: Inter, text-sm para mensajes, text-xs para timestamps
- Avatares: círculo bg-gpt-600 text-white con iniciales