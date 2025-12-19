<?php
namespace Modules\Chat\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Chat\App\Enums\ChatSessionStates;
use Modules\Chat\App\Models\ChatSession;

class ChatSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $states = ChatSessionStates::values(); // started, collecting_info, completed

        foreach ($states as $index => $state) {
            ChatSession::create([
                'current_state' => $state,
                'meta'          => [
                    'step' => $index,
                ],
            ]);
        }
    }
}
