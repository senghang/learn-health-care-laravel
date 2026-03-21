<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * BindSubdomainParameter
 *
 * Reads the subdomain from the current request host and sets it as a
 * URL default so that route() and redirect()->route() calls throughout
 * the clinic views don't require an explicit ['subdomain' => '...'] argument.
 *
 * Without this, any named route under Route::domain('{subdomain}.localhost')
 * throws:
 *   "Missing required parameter for [Route: X] [Missing parameter: subdomain]"
 *
 * Only runs when a real subdomain is present (not on the admin domain).
 */
class BindSubDomainParameter
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $parts = explode('.', $host);
        $subdomain = count($parts) >= 2 ? $parts[0] : null;

        // Don't bind on the admin domain — it has no {subdomain} parameter
        if ($subdomain && $subdomain !== 'admin') {
            URL::defaults(['subdomain' => $subdomain]);
        }

        return $next($request);
    }
}
