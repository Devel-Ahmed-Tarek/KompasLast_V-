<?php
namespace App\Providers;

use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;

class MiddlewareServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // تسجيل Middleware كـ global
        app(Kernel::class)->pushMiddleware(ForceJsonResponse::class);
    }
}
