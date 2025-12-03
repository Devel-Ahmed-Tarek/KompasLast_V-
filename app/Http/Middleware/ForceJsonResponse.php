<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceJsonResponse
{
    public function handle(Request $request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        $contentType = $response->headers->get('Content-Type');

        $excludedTypes = [
            'image', 'video', 'audio',
            'application/pdf',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/octet-stream',
        ];

        foreach ($excludedTypes as $type) {
            if (str_contains($contentType, $type)) {
                return $response;
            }
        }

        if (! $response instanceof \Illuminate\Http\JsonResponse) {
            return response()->json(
                $response->original ?? $response->getContent(),
                $response->status()
            );
        }

        return $response;
    }
}
