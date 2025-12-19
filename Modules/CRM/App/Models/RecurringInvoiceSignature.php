<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Employee\App\Models\Employee;

class RecurringInvoiceSignature extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'recurring_invoice_id',
        'type',
        'label',
        'assigned_signer_id',
        'notify_signer'
    ];

    public function recurringInvoice()
    {
        return $this->belongsTo(RecurringInvoice::class, 'recurring_invoice_id');
    }

    public function assignedSigner()
    {
        return $this->belongsTo(Employee::class, 'assigned_signer_id');
    }
}
