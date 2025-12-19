<?php
namespace Modules\Payroll\App\Enums;

enum PayrollComponentScopeModes: string {
    case SAME_AMOUNT_FOR_ALL        = 'Same Amount for All';
    case CSUTOM_AMOUNT_PER_EMPLOYEE = 'Custom Amount per Employee';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
