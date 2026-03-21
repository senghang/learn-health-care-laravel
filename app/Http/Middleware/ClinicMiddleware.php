<?php

namespace App\Http\Middleware;

use App\Models\ClinicModel;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class ClinicMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];

        if ($subdomain === 'admin') {  // change url direct from broswer
            // dd($subdomain);
            return $next($request); // Skip clinic logic
        }
        $clinic = ClinicModel::where('subdomain', $subdomain)
            ->where('is_active', true)
            ->first();

        if (!$clinic) {
            abort(404);
        }

        app()->instance('currentClinic', $clinic);

        // 🔐 SECURITY CHECK
        if (auth()->check()) {
            if (auth()->user()->clinic_id !== $clinic->id) {
                auth()->logout(); // force logout
                abort(403, 'Unauthorized clinic access.');
            }
        }
        return $next($request);
    }
}
