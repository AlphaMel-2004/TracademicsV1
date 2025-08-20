<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureRole;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Route::aliasMiddleware('role', EnsureRole::class);
    }
}



