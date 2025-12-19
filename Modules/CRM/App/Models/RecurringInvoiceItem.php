<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecurringInvoiceItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'recurring_invoice_id',
        'item_id',
        'start_date',
        'end_date',
        'recurring_interval',
        'recurrence_frequency',
        'description',
        'quantity',
        'rate',
        'tax_id',
        'tax_amount',
        'amount',
        'is_same_as_item',
        'discount_type',
        'discount_value',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function recurringInvoice()
    {
        return $this->belongsTo(RecurringInvoice::class, 'recurring_invoice_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function logs()
    {
        return $this->hasMany(RecurringInvoiceLog::class, 'recurring_invoice_item_id');
    }
}

