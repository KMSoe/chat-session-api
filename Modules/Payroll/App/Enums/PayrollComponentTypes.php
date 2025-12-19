<?php
namespace Modules\Payroll\App\Enums;

enum PayrollComponentTypes: string {
    case EARNING   = 'Earning';
    case DEDUCTION = 'Deduction';
    case OTHERS    = 'Others';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
