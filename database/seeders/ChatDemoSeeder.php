<?php

namespace Database\Seeders;

use App\Models\Chat\ChatCanal;
use App\Models\Chat\ChatMensaje;
use App\Models\User;
use App\Services\Chat\ChatService;
use Illuminate\Database\Seeder;

class ChatDemoSeeder extends Seeder
{
    public function run(): void
    {
        $chatService = app(ChatService::class);

        $this->command->info('Creating department channels...');
        $chatService->createDepartmentChannels();

        $this->command->info('Creating direction channel...');
        $chatService->createDirectionChannel();

        // Create a private channel between 2 users
        $user1 = User::where('status', 'active')->first();
        $user2 = User::where('status', 'active')->skip(1)->first();

        if ($user1 && $user2) {
            $this->command->info('Creating private channel...');
            $privado = ChatCanal::create([
                'tipo' => 'privado',
                'nombre' => "{$user1->name} & {$user2->name}",
                'descripcion' => 'Conversación privada',
            ]);
            $privado->miembros()->create(['user_id' => $user1->id]);
            $privado->miembros()->create(['user_id' => $user2->id]);

            // Add sample messages
            $messages = [
                ['user_id' => $user1->id, 'contenido' => 'Hola, ¿cómo va el proyecto?'],
                ['user_id' => $user2->id, 'contenido' => 'Todo en orden. Terminamos la cotización ayer.'],
                ['user_id' => $user1->id, 'contenido' => 'Excelente. ¿El cliente confirmó?'],
                ['user_id' => $user2->id, 'contenido' => 'Sí, @' . ($user1->name ?? 'Usuario') . ' confirmó ayer. Mañana enviamos la propuesta final.'],
                ['user_id' => $user1->id, 'contenido' => 'Perfecto. Mantenme al tanto.'],
            ];

            foreach ($messages as $msg) {
                $mensaje = $privado->mensajes()->create($msg);

                // Check @mentions
                preg_match_all('/@(\w+)/', $msg['contenido'], $matches);
                if (! empty($matches[1])) {
                    $mentionedUsers = User::whereIn('name', $matches[1])->get();
                    foreach ($mentionedUsers as $mu) {
                        $mensaje->menciones()->create(['user_id' => $mu->id]);
                    }
                }
            }
        }

        $this->command->info('Chat demo data seeded successfully.');
    }
}
