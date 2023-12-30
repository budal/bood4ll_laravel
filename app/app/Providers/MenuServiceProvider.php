<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\Menu\Menu;
use Illuminate\Support\Facades\Auth;

class MenuServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Menu::class, function ($app) {
            return new Menu(Auth::user());
        });
    }

    public function boot(): void
    {
    }
}
