<?php

namespace App\Services\Chat;

use App\Models\Chat\ChatCanal;
use App\Models\User;

class ChatService
{
    public function createProjectChannel(int $proyectoId, string $cpNumero, array $userIds): ChatCanal
    {
        $canal = ChatCanal::create([
            'tipo' => 'proyecto',
            'contexto_id' => $proyectoId,
            'nombre' => "Proyecto {$cpNumero}",
            'descripcion' => "Canal del proyecto {$cpNumero}",
        ]);

        foreach ($userIds as $userId) {
            $canal->miembros()->create(['user_id' => $userId]);
        }

        return $canal;
    }

    public function createDepartmentChannels(): void
    {
        $departamentos = User::whereNotNull('departamento')
            ->where('status', 'active')
            ->pluck('departamento')
            ->unique();

        foreach ($departamentos as $depto) {
            if (empty($depto)) continue;

            $canal = ChatCanal::firstOrCreate(
                ['tipo' => 'departamento', 'contexto_id' => null, 'nombre' => "Depto. {$depto}"],
                ['descripcion' => "Canal del departamento {$depto}"]
            );

            $users = User::where('departamento', $depto)
                ->where('status', 'active')
                ->get();

            $existingUserIds = $canal->miembros()->pluck('user_id')->toArray();

            foreach ($users as $user) {
                if (! in_array($user->id, $existingUserIds)) {
                    $canal->miembros()->create(['user_id' => $user->id]);
                }
            }
        }
    }

    public function createDirectionChannel(): ChatCanal
    {
        $canal = ChatCanal::firstOrCreate(
            ['tipo' => 'direccion', 'contexto_id' => null, 'nombre' => 'Dirección General'],
            ['descripcion' => 'Canal de Dirección General y Socios']
        );

        $roles = ['super_admin', 'direccion_general', 'socio', 'comite_socios'];
        $users = User::whereHas('roles', fn ($q) => $q->whereIn('name', $roles))
            ->where('status', 'active')
            ->get();

        $existingUserIds = $canal->miembros()->pluck('user_id')->toArray();

        foreach ($users as $user) {
            if (! in_array($user->id, $existingUserIds)) {
                $canal->miembros()->create(['user_id' => $user->id, 'rol_en_canal' => 'admin']);
            }
        }

        return $canal;
    }
}
