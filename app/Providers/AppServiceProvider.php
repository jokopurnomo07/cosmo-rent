<?php

namespace App\Providers;

use Midtrans\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');

        if (empty($serverKey)) {
            throw new \RuntimeException('MIDTRANS_SERVER_KEY is not set in .env');
        }

        Config::$serverKey    = $serverKey;
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized  = true;
        Config::$is3ds        = true;

        require_once base_path('app/Helpers/KagenouHelper.php');

        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }
    }
}