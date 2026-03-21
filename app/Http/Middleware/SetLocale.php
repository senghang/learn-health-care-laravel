<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * SetLocale
 *
 * Reads the desired locale from (in priority order):
 *   1. ?lang= query param  (allows per-request override, e.g. print pages)
 *   2. Session 'locale'   (set when user clicks language switcher)
 *   3. clinic.default_locale column
 *   4. 'km' fallback
 *
 * Must be registered AFTER ClinicMiddleware so currentClinic() is available.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang')
            ?? $request->session()->get('locale')
            ?? $this->clinicLocale()
            ?? 'km';

        // Whitelist: only allow km or en
        if (!in_array($locale, ['km', 'en'])) {
            $locale = 'km';
        }

        App::setLocale($locale);
        $request->session()->put('locale', $locale);

        return $next($request);
    }

    private function clinicLocale(): ?string
    {
        try {
            return app()->has('currentClinic')
                ? (app('currentClinic')->default_locale ?? null)
                : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
