<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('auth_user_id')) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|max:150',
            'password' => 'required|string|min:3',
        ]);

        $loginIdentifier = trim($request->input('email'));
        $password = $request->input('password');

        // 1. Anti-Brute Force Protection: 5 failed attempts per 60 seconds per IP
        $throttleKey = 'login_attempt:' . Str::transliterate(Str::lower($loginIdentifier)) . '|' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('error', "Security Lockout: Too many failed login attempts. Please wait {$seconds} seconds before trying again.")->withInput();
        }

        $user = User::where('email', $loginIdentifier)
                    ->orWhere('student_id', $loginIdentifier)
                    ->orWhere('id', $loginIdentifier)
                    ->first();

        if (!$user) {
            RateLimiter::hit($throttleKey, 60);
            return back()->with('error', 'Authentication Failed: Invalid credentials or account not found.')->withInput();
        }

        // 2. Check if account is suspended
        if (isset($user->status) && strtoupper($user->status) === 'SUSPENDED') {
            return back()->with('error', 'Account Suspended: This institutional account has been locked by administration.')->withInput();
        }

        // 3. Password Verification (supports hash and legacy match)
        $passwordMatch = ($user->password === $password) || Hash::check($password, $user->password);

        if (!$passwordMatch) {
            RateLimiter::hit($throttleKey, 60);
            $remaining = RateLimiter::remaining($throttleKey, 5);
            return back()->with('error', "Invalid password entered. ({$remaining} attempt(s) remaining before security lockout).")->withInput();
        }

        // 4. Role Authorization: Must be ADMIN, PRINCIPAL, or TEACHER/PROCTOR
        $allowedRoles = ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR', 'PROCTOR', 'TEACHER', 'FACULTY'];
        if (!in_array(strtoupper($user->role), $allowedRoles)) {
            RateLimiter::hit($throttleKey, 60);
            return back()->with('error', 'Access Restricted: Candidates must access via the Candidate Client App or Examination Portal.')->withInput();
        }

        // 5. Clear rate limiter on successful authentication
        RateLimiter::clear($throttleKey);

        // 6. Regenerate session ID to eliminate Session Fixation attacks
        $request->session()->regenerate();

        // 7. Store secure session payload with IP binding
        session([
            'auth_user_id' => $user->id,
            'auth_user_name' => $user->full_name,
            'auth_user_email' => $user->email,
            'auth_user_role' => strtoupper($user->role),
            'auth_user_avatar' => $user->avatar_url,
            'auth_user_ip' => $request->ip(),
            'auth_user_college' => $user->college_name,
            'auth_login_time' => now()->timestamp,
        ]);

        return redirect()->route('dashboard')->with('success', 'Authenticated successfully as ' . $user->full_name . ' (' . strtoupper($user->role) . ').');
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'You have been securely signed out.');
    }
}
