<?php

namespace App\Observers;

use App\Models\Chat\ChatCanal;
use App\Models\Proyectos\Proyecto;
use App\Models\User;
use App\Notifications\Chat\ChannelMemberAddedNotification;
use App\Notifications\Chat\ProjectChannelCreatedNotification;
use App\Services\Chat\ChatService;

class ProyectoChatObserver
{
    public function created(Proyecto $proyecto): void
    {
        $this->syncProjectChannel($proyecto);
    }

    public function updated(Proyecto $proyecto): void
    {
        // Reaccionar a cambios tanto en columnas legacy como en la relación
        // miembros (esta última se dispara manualmente via evento o listener)
        if ($proyecto->wasChanged([
            'director_dn_id', 'gerente_proyectos_id', 'gerente_operaciones_id',
            'ingeniero_costos_id', 'ingeniero_proyectos_id', 'trainee_id',
        ])) {
            $this->syncProjectChannel($proyecto);
        }
    }

    protected function syncProjectChannel(Proyecto $proyecto): void
    {
        $cpNumero = $proyecto->cp_numero ?? "CP-{$proyecto->id}";

        $canal = ChatCanal::firstOrCreate(
            ['tipo' => 'proyecto', 'contexto_id' => $proyecto->id],
            [
                'nombre'      => "Proyecto {$cpNumero}",
                'descripcion' => "Canal del proyecto {$cpNumero}",
            ]
        );

        // Preferir la tabla normalizada; si está vacía, usar columnas legacy
        $miembrosNuevos = $proyecto->miembros()->pluck('user_id')->filter()->unique()->toArray();

        if (empty($miembrosNuevos)) {
            $miembrosNuevos = array_filter([
                $proyecto->director_dn_id,
                $proyecto->gerente_proyectos_id,
                $proyecto->gerente_operaciones_id,
                $proyecto->ingeniero_costos_id,
                $proyecto->ingeniero_proyectos_id,
                $proyecto->trainee_id,
            ]);
        }

        $existingUserIds = $canal->miembros()->pluck('user_id')->toArray();
        $toAdd = array_diff($miembrosNuevos, $existingUserIds);

        foreach ($toAdd as $userId) {
            $canal->miembros()->create(['user_id' => $userId]);

            $user = User::find($userId);
            if ($user) {
                $user->notify(new ChannelMemberAddedNotification(
                    $canal->id,
                    $canal->nombre,
                ));
            }
        }

        if ($canal->wasRecentlyCreated) {
            foreach ($miembrosNuevos as $userId) {
                $user = User::find($userId);
                if ($user) {
                    $user->notify(new ProjectChannelCreatedNotification(
                        $canal->id,
                        $canal->nombre,
                        $cpNumero,
                    ));
                }
            }
        }
    }

    public function deleted(Proyecto $proyecto): void
    {
        ChatCanal::where('tipo', 'proyecto')
            ->where('contexto_id', $proyecto->id)
            ->delete();
    }
}
