<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserProvisionedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private ?string $tempPassword = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Bienvenido a GPT Services Platform')
            ->greeting("Hola {$notifiable->name},")
            ->line('Tu cuenta ha sido creada en la plataforma de GPT Services.');

        if ($this->tempPassword) {
            $mail->line('Puedes iniciar sesión con las siguientes credenciales:')
                ->line("**Email:** {$notifiable->email}")
                ->line("**Contraseña temporal:** {$this->tempPassword}")
                ->action('Iniciar sesión', url('/login'))
                ->line('Te recomendamos cambiar tu contraseña después de iniciar sesión.');
        } else {
            $mail->line('Ya puedes iniciar sesión con tu método de autenticación habitual.')
                ->action('Ir a la plataforma', url('/'));
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'user_provisioned',
            'message' => 'Tu cuenta ha sido creada en GPT Services Platform.',
            'has_temp_password' => $this->tempPassword !== null,
        ];
    }
}