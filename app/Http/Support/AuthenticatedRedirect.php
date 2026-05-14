<?php

namespace App\Http\Support;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Shared logic: detect session login on any web guard and redirect to the right dashboard.
 */
class AuthenticatedRedirect
{
    /** @var array<int, string> */
    protected static array $sessionGuards = [
        'web',
        'admin',
        'writer',
        'user',
        'reseller',
        'support',
    ];

    /** @return array<int, string> */
    public static function sessionGuards(): array
    {
        return self::$sessionGuards;
    }

    /**
     * First authenticated user found across session guards, or null.
     */
    public static function firstAuthenticatedUser(): ?object
    {
        foreach (self::$sessionGuards as $guard) {
            if (Auth::guard($guard)->check()) {
                return Auth::guard($guard)->user();
            }
        }

        return null;
    }

    /**
     * Redirect response for a logged-in user, or null if nobody is authenticated on any guard.
     */
    public static function redirectIfAuthenticated(): ?RedirectResponse
    {
        $user = self::firstAuthenticatedUser();
        if ($user === null) {
            return null;
        }

        return self::dashboardRedirectForUser($user);
    }

    public static function dashboardRedirectForUser(object $user): RedirectResponse
    {
        $userType = strtolower((string) ($user->user_type ?? ''));

        switch ($userType) {
            case 'admin':
                return redirect('/admin');
            case 'reseller':
                return redirect('/reseller');
            case 'user':
                return redirect('/user');
            case 'support':
                return redirect('/support');
            default:
                return redirect('/admin');
        }
    }
}
