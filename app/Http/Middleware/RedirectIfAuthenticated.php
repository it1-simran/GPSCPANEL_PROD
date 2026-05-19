<?php

namespace App\Http\Middleware;

use App\Http\Support\AuthenticatedRedirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  ...$guards
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        // Default `guest` middleware (no guard argument): check every web session guard so
        // users logged in via admin/reseller/writer guard cannot still open /login.
        if (count($guards) === 1 && $guards[0] === null) {
            $guards = AuthenticatedRedirect::sessionGuards();
        }

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return AuthenticatedRedirect::dashboardRedirectForUser(Auth::guard($guard)->user());
            }
        }

        return $next($request);
    }
}
