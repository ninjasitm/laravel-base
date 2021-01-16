<?php

namespace Nitm\Api\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FrameHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = @$next($request);
        if(!($response instanceof StreamedResponse) && !($response instanceof BinaryFileResponse)) {
            $response->header('X-Frame-Options', 'SAMEORIGIN');
        }
        return $response;
    }
}