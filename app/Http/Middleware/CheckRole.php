<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Check if user has the required role
        if ($user->role !== $role) {
            abort(403, 'Access denied. You do not have permission to access this page.');
        }

        // Additional check: ensure user is active
        if (!$user->is_active) {
            Auth::logout();
            return redirect('/login')->with('error', 'Your account has been deactivated. Please contact administrator.');
        }

        return $next($request);
    }
}
