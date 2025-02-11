<?php

namespace App\Providers;

use Midtrans\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-deL7LiPsBalCRdIg0AsiWpzo');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
        require_once base_path('app/Helpers/KagenouHelper.php');

        // if (env('APP_ENV') !== 'local') {
        //     URL::forceScheme('https');
        // }
    }
}
