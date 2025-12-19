<?php
namespace Modules\CRM\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Storage\App\Models\File;

class ReceivedPaymentFile extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'received_payment_files';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'received_payment_id',
        'file_id',
    ];

    /**
     * Get the payment that owns the file record.
     */
    public function receivedPayment()
    {
        return $this->belongsTo(ReceivedPayment::class, 'received_payment_id');
    }

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id');
    }
}
