<?php
namespace App\Enums;

enum ModuleNames: string {
    case EMPLOYEE_MANAGEMENT      = 'Employee Management';
    case EMPLOYEE                 = 'employee';
    case EMPLOYEE_GROUP           = 'employee-group';
    case DEPARTMENT               = 'department';
    case BRANCHES                 = 'branches';
    case LOCATIONS                = 'locations';
    case DESIGNATIONS             = 'designations';
    case GROUP_LIST_TREE          = 'group list tree';
    case DEPARTMENT_TREE          = 'department tree';
    case LEAVE_MANAGEMENT         = 'Leave Management';
    case LEAVE_REQUEST            = 'leave request';
    case LEAVE_BALANCE            = 'leave-balance';
    case LEAVE_TYPE_CONFIGURATION = 'leave-type-configuration';
    case LEAVE_TYPES              = 'leave-types';
    case TIME_ATTENDANCE          = 'Time & Attendance';
    case ATTENDANCE               = 'attendance';
    case LATE_REQUEST             = 'late request';
    case DUTY_ROSTER_TEMPLATE     = 'duty roster template';
    case DUTY_ROSTER              = 'duty roster';
    case HOLIDAYS                 = 'holidays';
    case SHIFT                    = 'shift';
    case HR_PROCESSES             = 'HR Processes';
    case CHECKLIST                = 'checklist';
    case OFFBOARDING              = 'offboarding';
    case CLAIMS                   = 'Claims';
    case CLAIM_TYPE               = 'claim type';
    case CLAIM                    = 'claim';
    case PAYROLL_MANAGEMENT       = 'Payroll Management';
    case PAYROLL_COMPONENT        = 'payroll-component';
    case PAYROLL_POLICY           = 'payroll-policy';
    case PAYSLIP_DESIGNER         = 'payslip-designer';
    case MAPPING_SETTINGS         = 'mapping-settings';
    case ORSO_SCHEMA              = 'orso-schema';
    case MPF_SCHEMA               = 'mpf-schema';
    case PAYROLL_LISTING          = 'payroll-listing';
    case PAYROLL_DEFINITION       = 'payroll-definition';
    case TAX_CALCULATION          = 'tax-calculation';
    case ENROLLMENT               = 'enrollment';
    case ADW_SETTINGS             = 'adw-settings';
    case FILING_MANAGEMENT        = 'filing-management';
    case DOCUMENTS                = 'Documents';
    case ANNOUNCEMENT             = 'Announcement';
    case PASSWORD                 = 'Password';
    case SETTING                  = 'Setting';
    case TAGS                     = 'tags';
    case ROLE_AND_PERMISSIONS     = 'role and permissions';
    case ACTIVITY_LOGS            = 'activity logs';
    case ATTRIBUTES               = 'attributes';
    case MODULE                   = 'module';
    case FOLDER                   = 'folder';
    case COMPANY                  = 'company';
    case APPROVAL_FLOW            = 'approval-flow';
    case OT_MANAGEMENT            = 'OT Management';
    case OVERTIME_REQUEST         = 'overtime-request';
    case OVERTIME_SETTINGS        = 'overtime-settings';
    case CRM                      = 'CRM';
    case OMNICHANNEL              = 'omnichannel';

    /**
     * Get all module values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
