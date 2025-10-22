<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Schema::defaultStringLength(191);
        // Set locale from session (fallback to config('app.locale'))
        App::setLocale(Session::get('locale', config('app.locale')));
        // Share the user session globally in all views
        View::composer('*', function ($view) {
            $view->with('authUser', Session::get('user'));
        });
    }
}
