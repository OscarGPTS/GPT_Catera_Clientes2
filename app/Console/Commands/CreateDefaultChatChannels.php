<?php

namespace App\Console\Commands;

use App\Services\Chat\ChatService;
use Illuminate\Console\Command;

class CreateDefaultChatChannels extends Command
{
    protected $signature = 'chat:create-defaults';

    protected $description = 'Create default department and direction chat channels';

    public function handle(ChatService $chatService): int
    {
        $this->info('Creating department channels...');
        $chatService->createDepartmentChannels();
        $this->info('Department channels created.');

        $this->info('Creating direction channel...');
        $chatService->createDirectionChannel();
        $this->info('Direction channel created.');

        $this->info('All default channels created successfully.');

        return self::SUCCESS;
    }
}
