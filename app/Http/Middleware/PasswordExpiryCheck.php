<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PasswordExpiryCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->password_changed_at) {
            if (now()->diffInDays($user->password_changed_at) >= 90) {
                return response()->json([
                    'error' => 'Password kedaluwarsa. Silakan ubah password.'
                ], 423);
            }
        }

        return $next($request);
    }
}
