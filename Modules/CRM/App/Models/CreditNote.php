<?php
namespace Modules\CRM\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nnjeim\World\Models\Currency;

class CreditNote extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'credit_notes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'contact_id',
        'credit_note_number',
        'credit_note_date',
        'reference_number',
        'status',
        'currency_id',
        'item_template_id',
        'customer_note',
        'terms_and_conditions',
        'description',
        'subtotal',
        'tax_id',
        'tax_amount',
        'discount_type',
        'discount_value',
        'grand_total',
        'credit_amount',
        'credit_applied',
        'remaining_credit',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'credit_note_date' => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function itemTemplate()
    {
        return $this->belongsTo(ItemTemplate::class, 'item_template_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id');
    }

    public function files()
    {
        return $this->hasMany(CreditNoteFile::class, 'credit_note_id');
    }

    public function items()
    {
        return $this->hasMany(CreditNoteItem::class, 'credit_note_id');
    }

    public function invoiceApplications()
    {
        return $this->hasMany(CreditNoteInvoiceApplication::class);
    }

    public function refundHistories()
    {
        return $this->hasMany(CreditNoteRefundHistory::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(CreditNoteActivityLog::class, 'credit_note_id');
    }

    public function comments()
    {
        return $this->hasMany(CreditNoteComment::class, 'credit_note_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
