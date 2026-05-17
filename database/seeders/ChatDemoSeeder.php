<?php

namespace Database\Seeders;

use App\Models\Chat\ChatCanal;
use App\Models\Chat\ChatCanalMiembro;
use App\Models\Chat\ChatLectura;
use App\Models\Chat\ChatMensaje;
use App\Models\User;
use App\Services\Chat\ChatService;
use Illuminate\Database\Seeder;

class ChatDemoSeeder extends Seeder
{
    public function run(): void
    {
        $chatService = app(ChatService::class);

        $users = User::where('status', 'active')->get();
        if ($users->count() < 2) {
            $this->command->warn('Se necesitan al menos 2 usuarios activos para el seeder de chat.');
            return;
        }

        $user1 = $users[0];
        $user2 = $users[1];
        $user3 = $users->count() >= 3 ? $users[2] : $users[0];
        $user4 = $users->count() >= 4 ? $users[3] : $users[1];
        $user5 = $users->count() >= 5 ? $users[4] : $users[0];

        $this->command->info('Creating department channels...');
        $chatService->createDepartmentChannels();

        $this->command->info('Creating direction channel...');
        $chatService->createDirectionChannel();

        $deptoCanal = ChatCanal::where('tipo', 'departamento')->first();

        if ($deptoCanal) {
            $this->command->info('Adding messages to department channel...');
            $deptoMsgs = [
                ['user_id' => $user1->id, 'contenido' => 'Buenos días equipo. Arrancamos con las revisiones de hoy.'],
                ['user_id' => $user2->id, 'contenido' => 'Listo. Tengo los reportes actualizados.'],
                ['user_id' => $user3->id, 'contenido' => '@' . $user1->name . ' te envié el avance del proyecto por correo.'],
                ['user_id' => $user1->id, 'contenido' => 'Gracias! Lo reviso en la tarde. Alguien tiene el archivo de cotizaciones actualizado?'],
                ['user_id' => $user4->id, 'contenido' => 'Sí, lo subo al shared drive en 10 minutos.'],
                ['user_id' => $user2->id, 'contenido' => 'Perfecto, avisen cuando esté para descargarlo.'],
            ];

            foreach ($deptoMsgs as $msgData) {
                $mensaje = $deptoCanal->mensajes()->create([
                    'user_id' => $msgData['user_id'],
                    'contenido' => $msgData['contenido'],
                    'created_at' => now()->subHours(count($deptoMsgs) - array_search($msgData, $deptoMsgs))->subMinutes(rand(5, 45)),
                ]);

                preg_match_all('/@(\w+)/', $msgData['contenido'], $matches);
                if (! empty($matches[1])) {
                    $mentionedUsers = User::whereIn('name', $matches[1])->get();
                    foreach ($mentionedUsers as $mu) {
                        $mensaje->menciones()->create(['user_id' => $mu->id]);
                    }
                }
            }
        }

        $dirCanal = ChatCanal::where('tipo', 'direccion')->first();

        if ($dirCanal) {
            $this->command->info('Adding messages to direction channel...');
            $dirMsgs = [
                ['user_id' => $user1->id, 'contenido' => 'Revisión de KPIs del trimestre. Los números van bien en general.'],
                ['user_id' => $user2->id, 'contenido' => 'Coincido. Hay oportunidad de mejora en el área de viáticos.'],
                ['user_id' => $user3->id, 'contenido' => 'Preparé un informe detallado. Lo comparto aquí próximamente.'],
            ];

            foreach ($dirMsgs as $msgData) {
                $mensaje = $dirCanal->mensajes()->create([
                    'user_id' => $msgData['user_id'],
                    'contenido' => $msgData['contenido'],
                    'created_at' => now()->subDays(1)->subHours(count($dirMsgs) - array_search($msgData, $dirMsgs)),
                ]);
            }
        }

        $allUserIds = $users->take(5)->pluck('id')->toArray();
        $proyectoCanal = ChatCanal::create([
            'tipo' => 'proyecto',
            'contexto_id' => null,
            'nombre' => 'Proyecto DN-025/26',
            'descripcion' => 'Canal del proyecto DN-025/26',
        ]);

        foreach ($allUserIds as $uid) {
            ChatCanalMiembro::create(['canal_id' => $proyectoCanal->id, 'user_id' => $uid]);
        }

        $this->command->info('Adding messages to project channel...');
        $proyMsgs = [
            ['user_id' => $user1->id, 'contenido' => 'Equipo, arrancamos con DN-025/26. @' . $user2->name . ' coordina la logística de sitio.'],
            ['user_id' => $user2->id, 'contenido' => 'Entendido. Mañana salgo a verificar las condiciones del terreno.'],
            ['user_id' => $user3->id, 'contenido' => 'Les comparto el plano actualizado del sitio.'],
            ['user_id' => $user2->id, 'contenido' => 'Llegamos a sitio, todo OK. @' . $user1->name . ' confirmar disponibilidad del equipo T-1200.'],
            ['user_id' => $user1->id, 'contenido' => 'Confirmado, el T-1200 ya está calibrado y listo para despacho.'],
            ['user_id' => $user4->id, 'contenido' => '¿Subimos la bitácora con las fotos del día?'],
            ['user_id' => $user1->id, 'contenido' => 'Sí, suban todo antes de las 6pm.'],
        ];

        foreach ($proyMsgs as $msgData) {
            $mensaje = $proyectoCanal->mensajes()->create([
                'user_id' => $msgData['user_id'],
                'contenido' => $msgData['contenido'],
                'created_at' => now()->subHours(count($proyMsgs) - array_search($msgData, $proyMsgs))->subMinutes(rand(10, 55)),
            ]);

            preg_match_all('/@(\w+)/', $msgData['contenido'], $matches);
            if (! empty($matches[1])) {
                $mentionedUsers = User::whereIn('name', $matches[1])->get();
                foreach ($mentionedUsers as $mu) {
                    $mensaje->menciones()->create(['user_id' => $mu->id]);
                }
            }
        }

        $this->command->info('Creating private DM channels...');
        $chatService->createPrivateChannel($user1->id, $user2->id);

        $privado = ChatCanal::where('tipo', 'privado')->first();

        if ($privado) {
            $dmMsgs = [
                ['user_id' => $user1->id, 'contenido' => 'Hola, ¿tienes un momento para revisar la cotización?'],
                ['user_id' => $user2->id, 'contenido' => 'Claro, déjame terminar esto y te aviso.'],
                ['user_id' => $user2->id, 'contenido' => 'Listo, ya estoy. ¿En qué punto quedamos ayer?'],
                ['user_id' => $user1->id, 'contenido' => 'En la sección 3. Necesito que confirmes los montos de materiales.'],
                ['user_id' => $user2->id, 'contenido' => 'Ok, lo reviso y te envío mis comentarios.'],
            ];

            foreach ($dmMsgs as $msgData) {
                $privado->mensajes()->create([
                    'user_id' => $msgData['user_id'],
                    'contenido' => $msgData['contenido'],
                    'created_at' => now()->subMinutes(rand(5, 120)),
                ]);
            }
        }

        $authUser = auth()->id() ?? $user1->id;
        foreach ([$deptoCanal, $dirCanal, $proyectoCanal, $privado] as $canal) {
            if ($canal) {
                ChatLectura::firstOrCreate(
                    ['canal_id' => $canal->id, 'user_id' => $authUser],
                    ['ultimo_mensaje_leido_id' => $canal->mensajes()->oldest()->first()->id ?? 0]
                );
            }
        }

        $this->command->info('Chat demo data seeded successfully.');
    }
}