<?php
namespace Modules\Payroll\App\Models\Benefit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Payroll\App\Models\PayrollComponent;

class MPFScheme extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "mpf_schemes";

    protected $fillable = [
        'code',
        'name',
        'trustee_id',
        'registration_no',
        'employer_name',
        'contact_person',
        'phone_dial_code',
        'phone_no',
        'address',
        'employer_participation_no',
        'employee_contribution_rate',
        'employer_contribution_rate',
        'minimum_income_level',
        'maximum_income_level',
        'eligibility_waiting_period_days',
        'voluntary_contributions_allowed',
        'voluntary_rate_employer',
        'voluntary_rate_employee',
        'contribution_based_on',
        'remittance_file_format',
        'effective_from_date',
        'termination_date',
        'remarks',
        'is_active',
    ];

    public function trustee()
    {
        return $this->belongsTo(MPFTrustee::class, 'trustee_id');
    }

    public function payrollComponents()
    {
        return $this->belongsToMany(
            PayrollComponent::class,
            'mpf_scheme_payroll_components',
            'mpf_scheme_id',
            'payroll_component_id'
        );
    }
}
