<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Auth;


class AdminAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // Check if SuperAdmin is logged in
        if (Auth::guard('superadmin')->check()) {
            // dd("checkAuth:supderadmin");
            return $next($request);
        }

        // Not logged in → redirect to admin login
        return redirect()->route('admin.login');
        
    }
}
