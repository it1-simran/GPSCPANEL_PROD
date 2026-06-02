<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if (!Auth::user()->hasPermission($permissionKey)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => "You don't have permission to perform this action!"
                ], 403);
            }
            return response()->view('unauthorized_access', [
                'error' => 403,
                'error_msg' => "You don't have permission to perform this action!"
            ]);
        }

        return $next($request);
    }
}
