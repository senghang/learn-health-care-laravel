<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckPermission Middleware
 *
 * Route-level RBAC enforcement. Checks if the authenticated user's role
 * has the required permission slug.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * REGISTRATION (bootstrap/app.php):
 *
 *   $middleware->alias([
 *       'can.do' => \App\Http\Middleware\CheckPermission::class,
 *   ]);
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * USAGE IN ROUTES:
 *
 *   // Single permission
 *   Route::get('/patients', [PatientController::class, 'index'])
 *       ->middleware('can.do:patients.view');
 *
 *   // Multiple permissions (ANY — user needs at least one)
 *   Route::get('/lab-or-imagery', [LabController::class, 'index'])
 *       ->middleware('can.do:laboratory.view,imagery.view');
 *
 *   // On a route group
 *   Route::prefix('employees')->middleware('can.do:employees.view')->group(fn() => ...);
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * PERMISSION FLOW:
 *
 *   Request → CheckPermission middleware
 *           → Auth::user()->role (RoleModel)
 *           → role->permissions (pivot: role_permission)
 *           → PermissionModel->slug matches?
 *           → YES: proceed | NO: 403
 *
 * ─────────────────────────────────────────────────────────────────────────────
 */
class CheckPermission
{
    /**
     * @param  string  ...$permissions  One or more permission slugs (comma-separated in route definition)
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        // Not authenticated — let auth middleware handle redirect
        if (!$user) {
            return redirect()->route('login');
        }

        // No role assigned — deny
        if (!$user->role) {
            abort(403, 'No role assigned. Contact your administrator.');
        }

        // Eager-load permissions if not already loaded
        if (!$user->role->relationLoaded('permissions')) {
            $user->role->load('permissions');
        }

        // Check if user has ANY of the required permissions
        $userSlugs = $user->role->permissions->pluck('slug');

        foreach ($permissions as $required) {
            if ($userSlugs->contains($required)) {
                return $next($request);
            }
        }

        // None matched — deny
        abort(403, 'You do not have permission to access this page.');
    }
}
