<?php

namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Payroll\Database\factories\PayrollComponentApplicableToFactory;

class PayrollComponentApplicableTo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'payroll_component_id',
        'scope',
        'target_id',
        'include_children',
    ];
    
    public function payrollComponent()
    {
        return $this->belongsTo(PayrollComponent::class);
    }
}
