<?php
namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditNoteRefundHistory extends Model
{
    protected $table = 'credit_note_refund_histories';

    protected $fillable = [
        'credit_note_id',
        'refunded_amount',
        'refunded_date',
        'reference_number',
        'payment_mode',
        'description',
    ];

    public function creditNote()
    {
        return $this->belongsTo(CreditNote::class);
    }
}
