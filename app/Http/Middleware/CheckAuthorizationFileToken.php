<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Device;


class CheckAuthorizationFileToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response('+#UNAUTHORIZED;', 401)
                ->header('Content-Type', 'text/plain');
        }
        $device = Device::where(['api_token'=>  $token,'id'=>$deviceId = $request->route('deviceId')])->first();
        if (!$device) {
            return response('+#UNAUTHORIZED;', 401)
                ->header('Content-Type', 'text/plain');
        }
        // // Attach device to request if needed
        // $request->device = $device;

        return $next($request);
    }
}
