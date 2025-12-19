<?php

namespace App\Listeners;

use App\Events\DatabaseForCacheUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ClearCache
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(DatabaseForCacheUpdated $event): void
    {
        if($event->cache_key) {
            cache()->forget($event->cache_key);
        }
    }
}
