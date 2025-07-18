<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  mixed  ...$roles
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $userRole = strtolower($user->tingkatan_otoritas ?? '');

        // Mode except (jika parameter pertama adalah "except")
        if (strtolower($roles[0]) === 'except') {
            $excludedRoles = array_map('strtolower', array_slice($roles, 1));

            if (in_array($userRole, $excludedRoles)) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            return $next($request);
        }

        // Default: hanya izinkan role tertentu
        $allowedRoles = array_map('strtolower', $roles);

        if (!in_array($userRole, $allowedRoles)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
