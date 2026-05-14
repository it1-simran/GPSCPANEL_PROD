<?php



namespace App\Http\Middleware;



use Closure;

use Illuminate\Support\Facades\Auth;



class CheckUserType
{
    // Define role hierarchy: Admin can access all, then Reseller, then Dealer, then User
    protected $roleHierarchy = [
        'admin' => ['admin', 'reseller', 'dealer', 'user', 'support'],
        'reseller' => ['reseller', 'dealer', 'user'],
        'dealer' => ['dealer', 'user'],
        'user' => ['user'],
        'support' => ['support'],
        /** Writers use the same app areas as end users; keep explicit so check.role:user passes. */
        'writer' => ['writer', 'user'],
    ];

    public function handle($request, Closure $next, ...$types)
    {
        if (!Auth::check()) {
            return response()->view('unauthorized_access', [
                'error' => 403,
                'error_msg' => 'Unauthorized access!'
            ]);
        }

        $userRole = strtolower(Auth::user()->user_type);
        $allowedRoles = $this->roleHierarchy[$userRole] ?? [];
        
        // Check if user's role allows access to any of the required types
        $hasAccess = false;
        foreach ($types as $type) {
            if (in_array(strtolower($type), $allowedRoles)) {
                $hasAccess = true;
                break;
            }
        }
        
        if (!$hasAccess) {
            return response()->view('unauthorized_access', [
                'error' => 403,
                'error_msg' => 'Unauthorized access!'
            ]);
        }
    
        return $next($request);
    }


}