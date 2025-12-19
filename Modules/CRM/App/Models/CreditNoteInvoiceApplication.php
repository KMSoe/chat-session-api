<?php
namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditNoteInvoiceApplication extends Model
{
    protected $table = 'credit_note_invoice_applications';

    protected $fillable = [
        'credit_note_id',
        'invoice_id',
        'credit_note_applied_date',
        'payment_amount',
    ];

    public function creditNote()
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
