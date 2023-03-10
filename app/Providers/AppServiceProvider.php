<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        require_once app_path().'/Helpers/functions.php';
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this -> app -> bind('path.public', function()
        {
            return base_path('public_html');
        });
    }
}
