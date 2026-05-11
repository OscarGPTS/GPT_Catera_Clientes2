<?php

namespace App\Livewire\Notificaciones;

use Livewire\Component;

class NotificationsIndex extends Component
{
    public $filter = 'all';
    public $search = '';

    public function getNotificationsProperty()
    {
        $query = auth()->user()->notifications()->latest();

        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($this->filter === 'menciones') {
            $query->where('data->type', 'chat_mencion');
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('data->title', 'like', "%{$search}%")
                  ->orWhere('data->message', 'like', "%{$search}%");
            });
        }

        return $query->paginate(30);
    }

    public function markAsRead($id)
    {
        $notif = auth()->user()->notifications()->find($id);
        if ($notif && is_null($notif->read_at)) {
            $notif->markAsRead();
        }
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
    }

    public function delete($id)
    {
        auth()->user()->notifications()->find($id)->delete();
    }

    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    public function render()
    {
        return view('livewire.notificaciones.notifications-index')
            ->layout('components.layouts.app');
    }
}
