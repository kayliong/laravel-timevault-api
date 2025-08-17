<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyApiRequest
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $appIdHeader = $request->header('lta-app-id');
        $apiKeyHeader = $request->header('x-api-key');

        $expectedAppId = env('LTA_APP_ID');
        $expectedApiKey = env('LTA_API_KEY');

        if ($appIdHeader !== $expectedAppId || $apiKeyHeader !== $expectedApiKey) {
            return response()->json([
                'error' => 'Unauthorized request. Invalid API credentials.'
            ], 401);
        }

        return $next($request);
    }
}
