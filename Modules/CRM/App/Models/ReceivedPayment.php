<?php
namespace Modules\CRM\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReceivedPayment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'received_payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'contact_id',
        'payment_no',
        'amount_received',
        'bank_charges',
        'payment_date',
        'payment_mode',
        'payment_received_date',
        'notes',
        'refunded_date',
        'reference_number',
        'description',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'payment_date'          => 'date',
        'payment_received_date' => 'date',
        'amount_received'       => 'decimal:2',
        'bank_charges'          => 'decimal:2',
    ];

    /**
     * Get the files associated with the invoice payment.
     */
    public function files()
    {
        return $this->hasMany(ReceivedPaymentFile::class, 'received_payment_id');
    }

    /**
     * Get the email communications associated with the invoice payment.
     */
    public function emailCommunications()
    {
        return $this->hasMany(ReceivedPaymentEmailCommunication::class, 'received_payment_id');
    }

    // You would typically add 'belongsTo' relationships here for company_id and contact_id

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function invoiceReceivedPayments()
    {
        return $this->hasMany(InvoiceReceivedPayment::class, 'received_payment_id')
            ->whereNot('status', 'refunded');
    }
    public function refundHistories()
    {
        return $this->hasMany(InvoiceReceivedPayment::class, 'received_payment_id')
            ->where('status', 'refunded');
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
