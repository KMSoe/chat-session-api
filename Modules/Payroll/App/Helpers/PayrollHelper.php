<?php
namespace Modules\Payroll\App\Helpers;

use Carbon\Carbon;
use Modules\Employee\App\Models\Department;
use Modules\Employee\App\Models\Designation;
use Modules\Employee\App\Models\Group;

class PayrollHelper
{

    public static function getPayCycleStartAndEnd($cycleDay, $target_date)
    {
        $target_date = $target_date ? Carbon::parse($target_date) : Carbon::now();
        $start_date  = null;
        $end_date    = null;

        // if ($target_date->day >= $cycleDay) {
        //     $start_date = Carbon::createFromFormat('Y-m-d', $target_date->format('Y-m') . '-' . $cycleDay);
        //     $end_date   = $start_date->copy()->addMonth()->subDay();
        // } else {
        //     $start_date = Carbon::createFromFormat('Y-m-d', $target_date->copy()->subMonth()->format('Y-m') . '-' . $cycleDay);
        //     $end_date   = $start_date->copy()->addMonth()->subDay();
        // }

        $start_date = Carbon::createFromFormat('Y-m-d', $target_date->format('Y-m') . '-' . $cycleDay);
        $end_date   = $start_date->copy()->addMonth()->subDay();

        return [
            'start_date'     => $start_date,
            'end_date'       => $end_date,
            'number_of_days' => $start_date->diffInDays($end_date) + 1,
        ];
    }

    public static function getUnpaidDeductionDetails($employee, $daily_rate, $unpaid_records)
    {
        $amount     = 0;
        $total_days = $unpaid_records->sum('total_days');

        if (! $employee || ! $employee->basic_salary || $total_days <= 0) {
            $amount = 0;
        } else {
            $amount = round($daily_rate * $total_days, 2);
        }

        return [
            'days'          => $total_days,
            'daily_rate'    => $daily_rate,
            'amount'        => $amount,
            'leave_details' => collect($unpaid_records)->map(function ($item) {
                return [
                    'date' => $item->date,
                ];
            }),
        ];
    }

    public static function getLeavePayDetails($employee, $daily_rate, $unpaid_records)
    {
        $amount     = 0;
        $total_days = $unpaid_records->sum('total_days');

        if (! $employee || ! $employee->basic_salary || $total_days <= 0) {
            $amount = 0;
        } else {
            $amount = round($daily_rate * $total_days, 2);
        }

        return [
            'days'          => $total_days,
            'daily_rate'    => $daily_rate,
            'amount'        => $amount,
            'leave_details' => collect($unpaid_records)->map(function ($item) {
                return [
                    'date' => $item->date,
                ];
            }),
        ];
    }

    public static function getAbsentDeductionDetails($employee, $daily_rate, $absent_records)
    {
        $amount     = 0;
        $total_days = count($absent_records);

        if (! $employee || ! $employee->basic_salary || $total_days <= 0) {
            $amount = 0;
        } else {
            $amount = round($daily_rate * $total_days, 2);
        }

        return [
            'days'           => $total_days,
            'daily_rate'     => $daily_rate,
            'amount'         => $amount,
            'absent_details' => collect($absent_records)->map(function ($item) {
                return [
                    'date' => $item->date,
                ];
            }),
        ];
    }

    public static function getEmployeeIdsFromApplicableTo(array $applicableTo)
    {
        $employeeIds = [];

        foreach ($applicableTo as $item) {
            switch ($item['scope']) {
                case 'group':
                    $employeeIds = array_merge(
                        $employeeIds,
                        Group::whereIn('id', $item['ids'])
                            ->with('employees')
                            ->get()
                            ->pluck('employees.*.id')
                            ->flatten()
                            ->toArray()
                    );
                    break;

                case 'department':
                    $employeeIds = array_merge(
                        $employeeIds,
                        Department::whereIn('id', $item['ids'])
                            ->with('employees')
                            ->get()
                            ->pluck('employees.*.id')
                            ->flatten()
                            ->toArray()
                    );
                    break;

                case 'designation':
                    $employeeIds = array_merge(
                        $employeeIds,
                        Designation::whereIn('id', $item['ids'])
                            ->with('employees')
                            ->get()
                            ->pluck('employees.*.id')
                            ->flatten()
                            ->toArray()
                    );
                    break;

                case 'employee':
                    $employeeIds = array_merge($employeeIds, $item['ids']);
                    break;
            }
        }

        return array_unique($employeeIds);
    }
}
