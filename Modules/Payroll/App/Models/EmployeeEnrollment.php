<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Employee\App\Models\Employee;
use Modules\Payroll\App\Models\Benefit\MPFScheme;
use Modules\Payroll\App\Models\Benefit\ORSOScheme;

class EmployeeEnrollment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id',
        'mpf_exempt',
        'account_type',
        'scheme_type',
        'scheme_id',
        'enrollment_date',
        'employee_contribution_start_date',
        'employer_contribution_start_date',
        'retirement_date',
        'employee_contribution_rate',
        'employer_contribution_rate',
        'voluntary_rate_employee',
        'voluntary_rate_employer',
        'remarks',
    ];

    protected $casts = [
        'mpf_exempt'                       => 'boolean',
        'enrollment_date'                  => 'date',
        'employee_contribution_start_date' => 'date',
        'employer_contribution_start_date' => 'date',
        'retirement_date'                  => 'date',
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('enrollment');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function mpfScheme()
    {
        return $this->belongsTo(MPFScheme::class, 'scheme_id');
    }

    public function orsoScheme()
    {
        return $this->belongsTo(ORSOScheme::class, 'scheme_id');
    }

    public function scheme()
    {
        if ($this->scheme_type == 'MPF') {
            return $this->belongsTo(MPFScheme::class, 'scheme_id');
        } else {
            return $this->belongsTo(ORSOScheme::class, 'scheme_id');
        }
    }
}
