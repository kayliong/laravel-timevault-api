<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ConfigModel;

class VerifyApiRequest
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $appIdHeader = $request->header('lta-app-id');
        $apiKeyHeader = $request->header('x-api-key');

        // get app id and api key from DB config
        $config = ConfigModel::getValue('api_security', 'app_id_api_key');
        $env = env('APP_ENV', 'local');
        $expectedAppId = $config[$env]['app_id'] ?? null;
        $expectedApiKey = $config[$env]['api_key'] ?? null;

        if ($appIdHeader !== $expectedAppId || $apiKeyHeader !== $expectedApiKey) {
            return response()->json([
                'error' => 'Unauthorized request. Invalid API credentials.'
            ], 401);
        }

        return $next($request);
    }
}
