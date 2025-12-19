<?php
namespace Modules\Chat\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ChatSession extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'session_uuid',
        'current_state', // default started
        'meta',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Automatically assign session_uuid on creation.
     */
    protected static function booted(): void
    {
        static::creating(function (self $chatSession) {
            if (empty($chatSession->session_uuid)) {
                $chatSession->session_uuid = (string) Str::uuid();
            }
        });
    }

    public static function findBySessionUuid(string $uuid): ?self
    {
        return self::where('session_uuid', $uuid)->firstOrFail();
    }

    public function scopefindBySessionUuid($query, $uuid)
    {
        return $query->where('session_uuid', $uuid);
    }

    public function updateState(string $state, array $meta = []): void
    {
        $this->current_state = $state;
        $this->meta          = array_merge($this->meta ?? [], $meta);
        $this->save();
    }
}
