<?php
namespace Modules\Payroll\App\Models\Benefit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MPFTrustee extends Model
{
    use SoftDeletes;

    protected $table = 'mpf_trustee';

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];
}
