<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserInvitedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private string $tempPassword,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invitación a GPT Services Platform')
            ->greeting("Hola {$notifiable->name},")
            ->line('Has sido invitado a unirte a la plataforma de GPT Services.')
            ->line('Tus credenciales de acceso son:')
            ->line("**Email:** {$notifiable->email}")
            ->line("**Contraseña temporal:** {$this->tempPassword}")
            ->action('Iniciar sesión', url('/login'))
            ->line('Te recomendamos cambiar tu contraseña después de iniciar sesión.')
            ->line('Si no esperabas esta invitación, puedes ignorar este correo.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'user_invited',
            'message' => 'Has sido invitado a GPT Services Platform.',
        ];
    }
}