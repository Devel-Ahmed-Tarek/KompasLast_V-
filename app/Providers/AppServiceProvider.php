<?php
namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
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
    public function boot()
    {
        $locale = request()->header('Accept-Language', 'en');
        if (! in_array($locale, ['en', 'de', 'fr', 'it'])) {
            $locale = 'en';
        }
        App::setLocale($locale);

        //
        Gate::before(function ($user) {
            if ($user->hasRole('Super Admin')) {
                return true;
            }
        });
    }
}
