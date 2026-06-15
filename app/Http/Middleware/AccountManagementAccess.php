<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountManagementAccess
{
    /**
     * Only Admin and Reseller can access Account Management
     * User/Dealer types are completely blocked
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access!'
                ], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Admin, Support, and Reseller can access Account Management.
        // User/Dealer accounts are blocked.
        if (!in_array($user->user_type, ['Admin', 'Support', 'Reseller'])) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => "You don't have permission to access Account Management!"
                ], 403);
            }
            return response()->view('unauthorized_access', [
                'error' => 403,
                'error_msg' => "You don't have permission to access Account Management!"
            ]);
        }

        return $next($request);
    }
}
