<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Http\Requests\LoginRequest;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use Laravel\Fortify\Contracts\LogoutResponse;
use App\Http\Responses\CustomLogoutResponse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind(
            FortifyLoginRequest::class,
            LoginRequest::class
        );
    }
}
