<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Storage\App\Classes\ObjectStorage;
use Modules\Storage\App\Models\File;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'logo_file_id',
        'display_name',
        'registration_name',
        'registration_no',
        'phone_dial_code',
        'phone_no',
        'email',
        'address',
        'region',
        'timezone',
        'employer_name',
        'employer_file_no',
        'employer_designation',
    ];

    protected $appends = ['phone_number'];

    public function getPhoneNumberAttribute()
    {
        if ($this->phone_dial_code && $this->phone_no) {
            return '+' . ltrim($this->phone_dial_code, '+') . ' ' . $this->phone_no;
        }

        return $this->phone_no ?? null;
    }

    public function logoFile()
    {
        return $this->belongsTo(File::class, 'logo_file_id');
    }
}
