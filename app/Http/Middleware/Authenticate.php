<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    protected function redirectTo(Request $request): ?string
    {
      
                if (! $request->expectsJson()) {

                    // If current host is admin domain
                    if ($request->getHost() === 'admin.localhost') {
                        return route('admin.login');
                    }

                    
                    // Default clinic login
                    return route('login');
                }

                return null;
            
    }
}
