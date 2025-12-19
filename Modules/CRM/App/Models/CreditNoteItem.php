<?php

namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\CRM\Database\factories\CreditNoteItemFactory;

class CreditNoteItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'credit_note_id',
        'item_id',
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

    public function creditNote()
    {
        return $this->belongsTo(CreditNote::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }
}
