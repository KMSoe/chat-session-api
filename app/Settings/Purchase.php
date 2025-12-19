<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class Purchase extends Settings
{

    public static function group(): string
    {
        return 'Purchase';
    }
}