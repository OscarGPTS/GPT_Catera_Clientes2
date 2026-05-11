<?php

namespace App\Livewire\Notificaciones;

use Livewire\Component;

class NotificationsDropdown extends Component
{
    public $notificaciones = [];
    public $noLeidas = 0;
    public $open = false;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = auth()->user();

        $this->notificaciones = $user->notifications()
            ->latest()
            ->take(20)
            ->get()
            ->map(function ($notif) {
                return [
                    'id' => $notif->id,
                    'type' => $notif->data['type'] ?? 'default',
                    'icon' => $notif->data['icon'] ?? 'bell',
                    'title' => $notif->data['title'] ?? 'Notificación',
                    'message' => $notif->data['message'] ?? '',
                    'link' => $notif->data['link'] ?? '#',
                    'read' => ! is_null($notif->read_at),
                    'created_at' => $notif->created_at->diffForHumans(),
                ];
            })
            ->toArray();

        $this->noLeidas = $user->unreadNotifications()->count();
    }

    public function markAsRead($id)
    {
        $notif = auth()->user()->notifications()->find($id);
        if ($notif && is_null($notif->read_at)) {
            $notif->markAsRead();
            $this->loadNotifications();
        }
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        $this->loadNotifications();
    }

    public function toggleOpen()
    {
        $this->open = ! $this->open;
    }

    public function close()
    {
        $this->open = false;
    }

    public function render()
    {
        return view('livewire.notificaciones.notifications-dropdown');
    }
}
