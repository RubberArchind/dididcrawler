<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class HttpsProtocol
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->secure() && (App::environment('production') || App::environment('staging'))) {
            // Check for proxies and load balancers
            if ($request->header('X-Forwarded-Proto') !== 'https') {
                return redirect()->secure($request->getRequestUri(), 301);
            }
        }

        $response = $next($request);
        
        // Add security headers
        if (App::environment('production') || App::environment('staging')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        }
        
        return $response;
    }
}