<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class HttpsProtocol
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->secure() && App::environment('production')) {
            // If we're behind a proxy that terminates SSL
            if ($request->header('X-Forwarded-Proto') === 'https') {
                return $next($request);
            }
            
            // Force HTTPS redirect
            $request->headers->set('X-Forwarded-Proto', 'https');
            return redirect()->secure($request->getRequestUri(), 301);
        }

        // Add HSTS header
        $response = $next($request);
        if (App::environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
        
        return $response;
    }
}