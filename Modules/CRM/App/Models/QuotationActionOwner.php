<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\Database\factories\QuotationActionOwnerFactory;
use Modules\Employee\App\Models\Employee;

class QuotationActionOwner extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'quotation_id',
        'action_owner_id',
        'level',
        'status',
        'comment',
        'approved_at',
        'rejected_at',
    ];
    
    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id');
    }

    public function actionOwner()
    {
        return $this->belongsTo(Employee::class, 'action_owner_id');
    }
}
