<?php

namespace Modules\Payroll\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Payroll\Database\factories\EmployeeTaxFilingHistoryFactory;

class EmployeeTaxFilingHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];
    
    protected static function newFactory(): EmployeeTaxFilingHistoryFactory
    {
        //return EmployeeTaxFilingHistoryFactory::new();
    }
}
