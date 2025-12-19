<?php
namespace Modules\Payroll\App\Enums;

enum PayrollComponentCalculationTypes: string {
    case FIXED_AMOUNT = 'Fixed Amount';
    case PERCENTAGE   = 'Percentage';
    case FORMULA      = 'Formula';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
