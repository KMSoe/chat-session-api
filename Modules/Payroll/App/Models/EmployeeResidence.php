<?php

namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Employee\App\Models\Employee;
use Modules\Payroll\Database\factories\EmployeeResidenceFactory;
use Spatie\Activitylog\Traits\LogsActivity;

class EmployeeResidence extends Model
{
   use SoftDeletes, LogsActivity;

    protected $table = 'employee_residences';

    protected $fillable = [
        'employee_id',
        'period',
        'nature',
        'start_time',
        'end_time',
        'rental_paid_by_employer',
        'rental_paid_by_employee',
        'rental_refunded_to_employee',
        'rental_paid_to_employer',
        'address',
    ];

    protected $casts = [
        'period' => 'integer',
        'start_time' => 'date',
        'end_time' => 'date',
        'rental_paid_by_employer' => 'decimal:2',
        'rental_paid_by_employee' => 'decimal:2',
        'rental_refunded_to_employee' => 'decimal:2',
        'rental_paid_to_employer' => 'decimal:2',
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('employee_residence');
    }

    /**
     * Relationships
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function taxFile()
    {
        return $this->hasOne(EmployeeTaxFile::class, 'employee_id', 'employee_id');
    }
}
