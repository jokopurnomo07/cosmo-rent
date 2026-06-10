<?php

namespace App\Providers;

use Midtrans\Config;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Models\Notification;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');

        if (!empty($serverKey)) {
            Config::$serverKey    = $serverKey;
            Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        }

        require_once base_path('app/Helpers/KagenouHelper.php');

        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }

        View::composer('layouts.admin.header', function ($view) {
            if (Auth::check()) {
                $notifications = Notification::where('user_id', Auth::id()) // ← selalu filter by user
                    ->latest()
                    ->take(15)
                    ->get();

                $view->with('notifications', $notifications);
            } else {
                $view->with('notifications', collect());
            }
        });
    }
}