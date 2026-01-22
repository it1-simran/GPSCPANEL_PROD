<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\JigModel;


class CheckAuthorizationTokenJig
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
        $data = json_decode($request->getContent(),true);
        // $sepData = explode(',', $data);
        if(!isset($data[35])){
            return response('+#UNAUTHORIZED;', 401)
                ->header('Content-Type', 'text/plain');
        }
        $imei = $data[1];
       
        if (!$token) {
            return response('+#UNAUTHORIZED;', 401)
                ->header('Content-Type', 'text/plain');
        }

        // Find the device with the provided token
        $device = JigModel::where(['api_token'=>  $token,'imei'=>$imei])->first();
        if (!$device) {
            return response('+#UNAUTHORIZED;', 401)
                ->header('Content-Type', 'text/plain');
        }

        // // Attach device to request if needed
        // $request->device = $device;

        return $next($request);
    }
}
