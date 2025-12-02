<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LanguageMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // تحقق من وجود لغة في الـ URL أو في رأس الطلب
        $locale = $request->route('locale', 'en'); // 'en' كلغة افتراضية
        app()->setLocale($locale);

        return $next($request);
    }
}
