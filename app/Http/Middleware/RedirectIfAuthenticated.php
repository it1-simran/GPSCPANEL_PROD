<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

     public function handle(Request $request, Closure $next, ...$guards)
        {
            $guards = empty($guards) ? [null] : $guards;

            foreach ($guards as $guard) {
                if (Auth::guard($guard)->check()) {
                    $user = Auth::guard($guard)->user();
                    $userType = strtolower($user->user_type);

                    switch ($userType) {
                        case 'admin':
                            return redirect('/admin');
                        case 'reseller':
                            return redirect('/reseller');
                        case 'user':
                            return redirect('/user');
                    }
                }
            }

            return $next($request);
        }
    // public function handle(Request $request, Closure $next, ...$guards)
    // {

    //        if (Auth::guard($guards)->check()) {
    //         $user = Auth::guard($guards)->user();
    //         $userType = strtolower($user->user_type);
    //         switch ($userType) {
    //             case 'admin':
    //                             // dd($user->user_type);
    //                 return redirect('/admin');
    //             case 'reseller':
    //                 return redirect('/reseller');
    //             case 'user':
    //                 return redirect('/user');
    //         }
    //     }
    //     // $guards = empty($guards) ? [null] : $guards;

    //     // foreach ($guards as $guard) {
    //     //     if (Auth::guard($guard)->check()) {
    //     //         return redirect(RouteServiceProvider::HOME);
    //     //     }
    //     // }

    //       return $next($request);
    // }
}
