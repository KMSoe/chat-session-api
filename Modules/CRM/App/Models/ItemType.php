<?php
namespace Modules\CRM\App\Models;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemType extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'item_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the items that belong to this item type.
     */
    public function items()
    {
        return $this->hasMany(Item::class, 'item_type');
    }

    /**
     * Get the custom properties associated with the item type.
     */
    public function attributes()
    {
        return $this->belongsToMany(
            Attribute::class,
            'item_type_attributes',
            'item_type_id',
            'attribute_id'
        );
    }
}
