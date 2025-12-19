<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodePrefix extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'module_id',
        'module_name',
        'prefix',
        'next_number',
        'type',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }
}
