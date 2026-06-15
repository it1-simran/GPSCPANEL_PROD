<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof InvalidSignatureException) {
            return response()->view('errors.link_expired', [
                'message' => 'This link has expired or is invalid.'
            ], 403);
        }

        if ($request->expectsJson() || $request->ajax()) {
            if ($exception instanceof \TypeError || $exception instanceof \ErrorException) {
                return response()->json([
                    'message' => 'Something went wrong while processing your request. Please refresh the page and try again.',
                ], 422);
            }
        }

        return parent::render($request, $exception);
    }
}
