<?php
namespace Modules\CRM\App\Models;

use App\Models\CustomValue;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'contact_code',
        'first_name',
        'last_name',
        'email',
        'work_phone_dial_code',
        'work_phone_number',
        'mobile_phone_dial_code',
        'mobile_phone_number',
        'password',
        'is_customer',
        'is_primary',
        'status',
        'company_id',
        'created_by',
        'updated_by',
    ];

    //  protected $hidden = [
    //     'password',
    // ];

    public function customValues()
    {
        return $this->hasMany(CustomValue::class, 'target_id')->whereHas('attribute.modules', function ($q) {
            $q->where('name', 'contact');
        });
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function creditNote()
    {
        return $this->hasOne(CreditNote::class, 'contact_id');
    }

    public function unpaidInvoices()
    {
        return $this->hasMany(Invoice::class, 'contact_id')->where('balance_due', '>', 0);
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
