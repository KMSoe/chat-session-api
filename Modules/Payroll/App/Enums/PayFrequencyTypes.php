<?php
namespace Modules\Payroll\App\Enums;

enum PayFrequencyTypes: string {
    case MONTHLY = 'Monthly';
    case WEEKLY  = 'Weekly';
    case DAILY   = 'Daily';
    case HOURLY  = 'Hourly';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
