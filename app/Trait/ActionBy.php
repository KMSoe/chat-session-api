<?php

namespace App\Trait;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait ActionBy {
    function createdBy() : BelongsTo {
        return $this->belongsTo(User::class, 'created_by');
    }
    function updatedBy() : BelongsTo {
        return $this->BelongsTo(User::class, 'updated_by');
    }
}