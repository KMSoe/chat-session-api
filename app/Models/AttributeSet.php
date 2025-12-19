<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'module_id',
        'is_active',
        'created_by',
        'updated_by',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'attribute_set_attributes')
                    ->withPivot('sort')
                    ->withTimestamps()
                    ->orderBy('sort');
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
