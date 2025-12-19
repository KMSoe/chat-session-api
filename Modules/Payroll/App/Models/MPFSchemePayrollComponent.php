<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MPFSchemePayrollComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'mpf_scheme_id',
        'payroll_component_id',
    ];
}
