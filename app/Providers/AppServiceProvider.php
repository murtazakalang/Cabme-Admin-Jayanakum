<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Requests;
use App\Observers\RideObserver;
use App\Models\Review;
use App\Models\Settings;
use App\Observers\ReviewObserver;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        setcookie('XSRF-TOKEN-AK', bin2hex(env('FIREBASE_APIKEY')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-AD', bin2hex(env('FIREBASE_AUTH_DOMAIN')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-DU', bin2hex(env('FIREBASE_DATABASE_URL')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-PI', bin2hex(env('FIREBASE_PROJECT_ID')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-SB', bin2hex(env('FIREBASE_STORAGE_BUCKET')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-MS', bin2hex(env('FIREBASE_MESSAAGING_SENDER_ID')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-AI', bin2hex(env('FIREBASE_APP_ID')), time() + 3600, "/"); 
        setcookie('XSRF-TOKEN-MI', bin2hex(env('FIREBASE_MEASUREMENT_ID')), time() + 3600, "/"); 
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Requests::observe(RideObserver::class);
        Review::observe(ReviewObserver::class);

        View::composer('*', function ($view) {
            $app_setting = Settings::first();

            setcookie('admin_panel_color', $app_setting->adminpanel_color, time() + 3600, "/");
            setcookie('admin_panel_sec_color', $app_setting->adminpanel_sec_color, time() + 3600, "/");

            $app_setting->pusher_settings = [
                'pusher_app_id' => env('PUSHER_APP_ID'),
                'pusher_key'    => env('PUSHER_APP_KEY'),
                'pusher_secret' => env('PUSHER_APP_SECRET'),
                'pusher_cluster'=> env('PUSHER_APP_CLUSTER'),
            ];
            $app_setting->save();

            $view->with([
                'app_setting' => $app_setting,
                'admin_panel_color' => $app_setting->adminpanel_color ? $app_setting->adminpanel_color : '#facc15',
                'admin_panel_sec_color' => $app_setting->adminpanel_sec_color ? $app_setting->adminpanel_sec_color : '#6365f1',
            ]);
        });
    }
}
