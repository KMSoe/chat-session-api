<?php
namespace Modules\CRM\App\Models;

use App\Models\CustomValue;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'amount',
        'item_type_id',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the item type that this item belongs to.
     */
    public function itemType()
    {
        return $this->belongsTo(ItemType::class, 'item_type_id');
    }

    public function customValues()
    {
        return $this->hasMany(ItemCustomValue::class, 'target_id');
    }

    public function itemTemplates()
    {
        return $this->belongsToMany(ItemTemplate::class, 'item_template_items', 'item_id', 'item_template_id');
    }

}
