<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class HierarchyAccess
{
    /**
     * Verify that the authenticated user can manage the target user
     * Used when accessing user management pages where {user_id} is provided
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $paramName  The route parameter name (default: 'user_id')
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $paramName = 'user_id')
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

        $currentUser = Auth::user();
        $targetUserId = $request->route($paramName);

        if (!$targetUserId) {
            return $next($request);
        }

        $targetUser = User::find($targetUserId);

        if (!$targetUser) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found!'
                ], 404);
            }
            return redirect()->back()->with('error', 'User not found!');
        }

        // Check if current user can manage target user
        if (!$currentUser->canManage($targetUser)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => "You don't have permission to manage this user!"
                ], 403);
            }
            return response()->view('unauthorized_access', [
                'error' => 403,
                'error_msg' => "You don't have permission to manage this user!"
            ]);
        }

        return $next($request);
    }
}
