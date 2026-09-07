<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');

        if (!$userId || strtoupper($userRole) !== 'ADMIN') {
            abort(403, 'Unauthorized Access: You do not have Super Administrator privileges to perform this operation.');
        }

        return $next($request);
    }
}
