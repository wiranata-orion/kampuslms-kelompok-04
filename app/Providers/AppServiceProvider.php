<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
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

        
        Model::shouldBeStrict(! $this->app->isProduction());

        
        Blade::if('role', function (string ...$roles) {
            $user = auth()->user();

            return $user && in_array($user->role, $roles, true);
        });
    }
}