<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Employee\App\Models\Employee;

class InvoiceActionOwner extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'invoice_id',
        'action_owner_id',
        'level',
        'status',
        'comment',
        'approved_at',
        'rejected_at',
    ];
    
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function actionOwner()
    {
        return $this->belongsTo(Employee::class, 'action_owner_id');
    }
}
