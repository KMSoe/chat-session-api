<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\Database\factories\RecurringInvoiceLogFactory;

class RecurringInvoiceLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'recurring_invoice_id',
        'recurring_invoice_item_id',
        'invoice_id',
        'generated_on',
    ];

    protected $casts = [
        'generated_on' => 'date',
    ];
    
    public function recurringInvoice()
    {
        return $this->belongsTo(RecurringInvoice::class, 'recurring_invoice_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
