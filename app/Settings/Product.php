<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class Product extends Settings
{

    public string $tax;
    
    public bool $amount;
    
    public static function group(): string
    {
        return 'product';
    }
}