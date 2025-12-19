<?php
namespace Modules\Payroll\App\Enums;

enum PayrollStatusTypes: string {
    case NOT_CALCULATED    = 'Not Calculated';
    case CALCULATED        = 'Calculated';
    case LOCKED            = 'Locked';
    case PAYSLIP_GENERATED = 'Payslip Generated';
    case PAYSLIP_SENT      = 'Payslip Sent';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
