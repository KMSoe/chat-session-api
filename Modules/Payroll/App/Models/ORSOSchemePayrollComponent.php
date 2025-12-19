<?php
namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ORSOSchemePayrollComponent extends Model
{
    use HasFactory;

    protected $table = 'orso_scheme_payroll_components';

    protected $fillable = [
        'orso_scheme_id',
        'payroll_component_id',
    ];
}
