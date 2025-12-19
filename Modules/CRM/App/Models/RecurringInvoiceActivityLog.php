<?php

namespace Modules\CRM\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurringInvoiceActivityLog extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'recurring_invoice_id',
        'description',
        'action_by',
    ];
    
    public function recurringInvoice()
    {
        return $this->belongsTo(RecurringInvoice::class);
    }

    public function actionBy()
    {
        return $this->belongsTo(User::class, 'action_by');
    }
}

