<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Employee\App\Models\Employee;
use Spatie\Activitylog\Traits\LogsActivity;

class EmployeeTaxFile extends Model
{
    use LogsActivity, SoftDeletes;

    protected $table = 'employee_tax_files';

    protected $fillable = [
        'employee_id',
        'passport_no',
        'passport_place_of_issue',
        'spouse_full_name',
        'spouse_id_card',
        'spouse_passport_no',
        'spouse_passport_place_of_issue',
        'region_code',
        'principal_employer_name',
        'tax_identity',
        'other_income_name',
        'same_as_address',
        'postal_address',
        'employer_provides_residence',
    ];

    protected $casts = [
        'same_as_address'             => 'boolean',
        'employer_provides_residence' => 'boolean',
    ];

    public function getActivitylogOptions(): \Spatie\Activitylog\LogOptions
    {
        return \Spatie\Activitylog\LogOptions::defaults()
            ->logAll()
            ->useLogName('employee_tax_file');
    }

    /**
     * Relationships
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function residences()
    {
        return $this->hasMany(EmployeeResidence::class, 'employee_id', 'employee_id');
    }
}
