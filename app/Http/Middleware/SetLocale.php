<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * SetLocale Middleware
 *
 * Sets the application locale (km or en) for every request.
 *
 * Priority order (first match wins):
 *   1. ?lang= query string  — per-request override (useful for print pages)
 *   2. session('locale')    — user's chosen language
 *   3. clinic.default_locale — clinic's configured default
 *   4. 'km'                 — system fallback
 *
 * Must run AFTER ClinicMiddleware (so currentClinic() is available).
 * Registered as 'locale' alias in bootstrap/app.php.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * WHY LANGUAGE SWITCH SOMETIMES FAILS
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * Common causes:
 *
 * 1. Middleware order wrong:
 *    ❌  ['web', 'locale', 'clinic']   → clinic not bound when locale runs
 *    ✅  ['web', 'clinic', 'locale']   → correct order
 *
 * 2. Lang key returns array (htmlspecialchars error):
 *    ❌  'patient' => ['title' => '...']  then  {{ __('app.patient') }}
 *    ✅  Use  {{ __('app.patient.title') }}  for nested keys
 *    ✅  Or use flat keys: 'patient_title' => '...'
 *
 * 3. Missing lang file:
 *    ❌  lang/km/app.php doesn't exist
 *    ✅  Both lang/km/app.php and lang/en/app.php must exist
 *
 * 4. Locale not in whitelist:
 *    ❌  session stores 'kh' instead of 'km'
 *    ✅  Whitelist: ['km', 'en']
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        // Persist to session so next request remembers the choice
        $request->session()->put('locale', $locale);

        // Apply to Laravel's app locale (affects __(), trans(), etc.)
        App::setLocale($locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $whitelist = ['km', 'en'];

        // Priority 1: Explicit query parameter (?lang=en)
        if ($lang = $request->query('lang')) {
            if (in_array($lang, $whitelist)) {
                return $lang;
            }
        }

        // Priority 2: Session (set when user clicks the language switcher)
        if ($lang = $request->session()->get('locale')) {
            if (in_array($lang, $whitelist)) {
                return $lang;
            }
        }

        // Priority 3: Clinic's configured default
        try {
            if (app()->has('currentClinic')) {
                $clinicLocale = app('currentClinic')->default_locale ?? null;
                if ($clinicLocale && in_array($clinicLocale, $whitelist)) {
                    return $clinicLocale;
                }
            }
        } catch (\Throwable) {
            // Clinic not resolved yet — fall through to default
        }

        // Priority 4: System default
        return 'km';
    }
}
