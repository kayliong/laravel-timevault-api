<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;
use App\Models\ConfigModel;

class RateLimit
{
    protected $maxAttempts = 60;   // max requests per minute
    protected $resetWindow = 1;   // window size for reset the throttle

    public function __construct()
    {
        // load rate limit settings from database
        $this->maxAttempts = ConfigModel::getValue('rate_limit', 'max_attempt', $this->maxAttempts);
        $this->resetWindow = ConfigModel::getValue('rate_limit', 'reset_window', $this->resetWindow);
    }

    public function handle($request, Closure $next)
    {
        $ip  = $request->ip();
        $key = 'rate_limit:' . $ip;
        $ttlSeconds = $this->resetWindow * 60;

        $attempts = Cache::get($key, 0);

        if ($attempts >= $this->maxAttempts) {
            $resp = response()->json([
                'message' => 'Too many requests. Please try again later.',
            ], Response::HTTP_TOO_MANY_REQUESTS);

            // nice-to-have headers
            $resp->headers->set('Retry-After', $ttlSeconds);
            $resp->headers->set('X-RateLimit-Limit', $this->maxAttempts);
            $resp->headers->set('X-RateLimit-Remaining', 0);

            return $resp;
        }

        // update counter with an absolute expiration time (sliding window)
        Cache::put($key, $attempts + 1, Carbon::now()->addSeconds($ttlSeconds));

        $remaining = max(0, $this->maxAttempts - ($attempts + 1));

        $response = $next($request);
        $response->headers->set('X-RateLimit-Limit', $this->maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $remaining);

        return $response;
    }
}
