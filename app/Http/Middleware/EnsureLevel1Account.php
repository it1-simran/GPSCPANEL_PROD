<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * PO / KYC / SKU are owned by the Level 1 account (created directly under
 * Admin) that a customer relationship belongs to — sub-accounts created by a
 * Reseller/User (parent_user_id points at a non-Admin account) don't get
 * their own; those stay with whichever Level 1 account created them.
 */
class EnsureLevel1Account
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if ($user && !$user->isLevel1()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['message' => 'Not available for sub-accounts.'], 403);
            }
            return response()->view('unauthorized_access', [
                'error' => 403,
                'error_msg' => 'This feature is only available to the primary (Level 1) account, not sub-accounts.',
            ], 403);
        }

        return $next($request);
    }
}
