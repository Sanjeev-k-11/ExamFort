<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminProctorAuth
{
    /**
     * Handle an incoming request with strict anti-bypass validation.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = session('auth_user_id');
        $userRole = session('auth_user_role');
        $sessionIp = session('auth_user_ip');
        $currentIp = $request->ip();

        $allowedRoles = ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR', 'PROCTOR', 'TEACHER', 'FACULTY'];

        // 1. Check if authenticated
        if (!$userId || !in_array(strtoupper($userRole), $allowedRoles)) {
            session()->flush();
            return redirect()->route('login')->with('error', 'Access Denied: Please log in with a valid faculty, Dean/Principal, or administrator credential.');
        }

        // 2. Anti-Session Hijacking Check (Verify IP match)
        if ($sessionIp && $sessionIp !== $currentIp) {
            session()->flush();
            return redirect()->route('login')->with('error', 'Security Alert: Session IP mismatch detected. Please re-authenticate.');
        }

        // 3. Real-Time Account Status Check (Check if user was suspended or deleted)
        $liveUser = User::find($userId);
        if (!$liveUser) {
            session()->flush();
            return redirect()->route('login')->with('error', 'Account no longer exists in database.');
        }

        if (isset($liveUser->status) && strtoupper($liveUser->status) === 'SUSPENDED') {
            session()->flush();
            return redirect()->route('login')->with('error', 'Account Suspended: Your institutional access has been temporarily revoked by system administration.');
        }

        return $next($request);
    }
}
