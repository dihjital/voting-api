<?php

namespace App\Providers;

use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;

use Laravel\Lumen\Http\ResponseFactory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    public function boot()
    {
        Str::macro('isValidUuid', function ($value) {
            return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
        });

        ResponseFactory::macro('success', function (string $message) {
            // Do something useful here ...
        });

        ResponseFactory::macro('error', function (string $message, int $responseCode = 500) {
            return response()->json([
                'status' => 'error',
                'message' => $message,
            ], $responseCode);
        });
    }
}