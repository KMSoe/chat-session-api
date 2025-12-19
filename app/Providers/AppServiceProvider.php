<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register application services here if needed
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Custom validation rule: exists_or_null
        Validator::extend(
            'exists_or_null',
            function (string $attribute, $value, array $parameters): bool {
                if (empty($value)) { // Handles null, 0, or empty values
                    return true;
                }

                $validator = Validator::make(
                    [$attribute => $value],
                    [$attribute => 'exists:' . implode(',', $parameters)]
                );

                return ! $validator->fails();
            }
        );

        // Shared variable for views
        $sharedMessage = 'This is a shared variable';

        // Share with all views
        View::share('myname', $sharedMessage);
    }
}
