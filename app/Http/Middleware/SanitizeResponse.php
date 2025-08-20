<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeResponse
{
    /**
     * Headers to remove from response.
     */
    protected $headersToRemove = [
        'Server',
        'X-Powered-By',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        foreach ($this->headersToRemove as $header) {
            $response->headers->remove($header);
        }

        return $response;
    }
}
