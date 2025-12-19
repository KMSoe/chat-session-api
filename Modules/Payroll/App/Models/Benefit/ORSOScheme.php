<?php
namespace Modules\Payroll\App\Models\Benefit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Payroll\App\Models\PayrollComponent;

class ORSOScheme extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "orso_schemes";

    protected $fillable = [
        'code',
        'name',
        'trustee_id',
        'scheme_type',
        'registration_no',
        'employer_participation_no',
        'employee_contribution_required',
        'employee_contribution_rate',
        'employer_contribution_rate',
        'service_duration_from_year',
        'service_duration_from_months',
        'service_duration_to_year',
        'service_duration_to_months',
        'vested_percentage_of_employer_contribution',
        'benefit_formula',
        'eligibility_waiting_period_days',
        'voluntary_contributions_allowed',
        'voluntary_rate_employer',
        'voluntary_rate_employee',
        'contribution_frequency',
        'contribution_based_on',
        'effective_from_date',
        'termination_date',
        'remarks',
        'is_active',
    ];

     protected $casts = [
        'employee_contribution_required' => 'boolean',
        'voluntary_contributions_allowed' => 'boolean',
        'is_active' => 'boolean',
        'effective_from_date' => 'date',
        'termination_date' => 'date',
    ];

    public function trustee()
    {
        return $this->belongsTo(MPFTrustee::class, 'trustee_id');
    }

    public function payrollComponents()
    {
        return $this->belongsToMany(
            PayrollComponent::class,
            'orso_scheme_payroll_components',
            'orso_scheme_id',
            'payroll_component_id'
        );
    }
}
