<?php



namespace App\Http\Middleware;



use Closure;

use Illuminate\Support\Facades\Auth;



class CheckUserType
{

    public function handle($request, Closure $next, ...$types)
    {
        if (!Auth::check() || !in_array(strtolower(Auth::user()->user_type), $types)) {
             return response()->view('unauthorized_access', [
                'error' => 403,
                'error_msg' => 'Unauthorized access!'
            ]);
        }
    
        return $next($request);
    }


}