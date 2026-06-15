<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\PermissionHelper;

/**
 * CheckPermission Middleware
 *
 * Enhanced with real-time permission validation.
 * Validates permissions directly from the database on every request,
 * ensuring that permission changes take effect immediately.
 */
class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permissionKey
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permissionKey)
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access!'
                ], 401);
            }
            return response()->view('unauthorized_access', [
                'error' => 401,
                'error_msg' => 'Unauthorized access!'
            ]);
        }

        // Refresh user from database to ensure we have latest data
        // This prevents stale user data from cached models
        $user = Auth::user();
        $user = $user->fresh(); // Reload from database

        if (!$user) {
            // User was deleted, log them out
            Auth::logout();
            $request->session()->invalidate();
            return response()->view('unauthorized_access', [
                'error' => 401,
                'error_msg' => 'Your account is no longer available.'
            ]);
        }

        // Admin and Support always bypass permission checks — staff accounts
        // have full access; permission gating only applies to Reseller and User.
        if (in_array($user->user_type, ['Admin', 'Support'], true)) {
            return $next($request);
        }

        PermissionHelper::flushCache();

        if (!PermissionHelper::hasPermission($permissionKey, $user)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => "You don't have permission to perform this action!"
                ], 403);
            }

            return response()->view('unauthorized_access', [
                'error' => 403,
                'error_msg' => "You don't have permission to perform this action!"
            ], 403);
        }

        return $next($request);
    }
}
