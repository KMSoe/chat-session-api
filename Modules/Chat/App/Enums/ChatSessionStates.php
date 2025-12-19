<?php
namespace Modules\Chat\App\Enums;

enum ChatSessionStates: string {
    case STARTED         = 'started';
    case COLLECTING_INFO = 'collecting_info';
    case COMPLETED       = 'completed';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
