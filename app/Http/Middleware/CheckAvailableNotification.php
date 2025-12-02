<?php
namespace App\Http\Middleware;

use App\Helpers\HelperFunc;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAvailableNotification
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->available_notification) {
            return HelperFunc::sendResponse(403, __('messages.notifications_disabled'), ['message' => __('messages.notifications_disabled')]);
        }

        return $next($request);
    }
}
