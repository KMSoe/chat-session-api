<?php
namespace Modules\Payroll\App\Enums;

enum PayrollHoursSourceTypes: string {
    case FETCH_FROM_ATTENDANCE = 'Fetch from Attendance';
    case MANUAL_INPUT          = 'Manual Input';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
