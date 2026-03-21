<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * BindSubdomainParameter
 *
 * Routes are registered under Route::domain('{subdomain}.localhost').
 * Every named route therefore has an implicit {subdomain} parameter.
 *
 * Without this middleware, every call to route('dashboard') throws:
 *   "Missing required parameter for [Route: dashboard] [Missing parameter: subdomain]"
 *
 * This middleware reads the actual subdomain from the incoming request
 * and registers it as a URL default so all route() / action() / redirect()->route()
 * calls across controllers AND Blade views work without passing it explicitly.
 */
class BindSubdomainParameter
{
    public function handle(Request $request, Closure $next): Response
    {
        $host      = $request->getHost();                // e.g. "dtc.localhost"
        $subdomain = explode('.', $host)[0];             // e.g. "dtc"

        // Register as a global URL default.
        // This fills {subdomain} automatically in ALL route() calls.
        URL::defaults(['subdomain' => $subdomain]);

        return $next($request);
    }
}
