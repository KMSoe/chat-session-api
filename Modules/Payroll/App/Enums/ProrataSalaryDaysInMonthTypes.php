<?php
namespace Modules\Payroll\App\Enums;

enum ProrataSalaryDaysInMonthTypes: string {
    case TOTAL_CALENDAR_DAYS_IN_MONTH = 'total_calendar_days_in_month';
    case TOTAL_WORKING_DAYS_IN_MONTH  = 'total_working_days_in_month';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
