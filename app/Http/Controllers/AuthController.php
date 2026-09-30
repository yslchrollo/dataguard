<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'email.unique' => 'This email address is already registered in DataGuard.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.min' => 'The password must be at least 8 characters.',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user', // Enforce default Regular Employee / User role
            'department' => 'Operations',
            'status' => 'active',
        ]);

        ActivityLog::record(
            'auth_registered',
            'Auth',
            "New employee account self-registered: {$user->name} ({$user->email}) with role 'user'.",
            ['user_id' => $user->id, 'email' => $user->email, 'role' => 'user'],
            $user
        );

        Auth::login($user);
        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);

        return redirect()->route('dashboard')->with('success', "Welcome to DataGuard, {$user->name}! Your employee account has been activated.");
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our security records.',
            ])->onlyInput('email');
        }

        if (!$user->isActive()) {
            return back()->with('error', 'This account has been deactivated. Contact an administrator.');
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        $user->update(['last_login_at' => now()]);

        ActivityLog::record(
            'auth_login_success',
            'Auth',
            "User {$user->name} ({$user->role}) logged in successfully.",
            ['email' => $user->email, 'role' => $user->role],
            $user
        );

        return redirect()->intended(route('dashboard'))->with('success', "Welcome back, {$user->name}!");
    }

    public function quickLogin(Request $request, string $role)
    {
        $allowedRoles = ['admin', 'analyst', 'user'];
        if (!in_array($role, $allowedRoles)) {
            return redirect()->route('login')->with('error', 'Invalid demonstration role.');
        }

        $demoEmails = [
            'admin' => 'admin@dataguard.corp',
            'analyst' => 'analyst@dataguard.corp',
            'user' => 'user@dataguard.corp',
        ];

        $user = User::where('email', $demoEmails[$role] ?? '')
            ->where('status', 'active')
            ->first() 
            ?? User::where('role', $role)->where('status', 'active')->first();

        if (!$user || !$user->isActive()) {
            return redirect()->route('login')->with('error', "No active demo account found for role '{$role}'.");
        }

        Auth::login($user);
        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);

        ActivityLog::record(
            'auth_quick_switch',
            'Auth',
            "Demonstration login switched to role {$user->role} ({$user->name}).",
            ['role' => $user->role],
            $user
        );

        return redirect()->route('dashboard')->with('success', "Authenticated as {$user->name} (" . strtoupper($user->role) . " mode)");
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            ActivityLog::record(
                'auth_logout',
                'Auth',
                "User {$user->name} logged out.",
                [],
                $user
            );
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been securely logged out.');
    }
}
