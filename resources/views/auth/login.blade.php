@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
<div class="w-full max-w-md space-y-6">
    <!-- Brand Header -->
    <div class="text-center space-y-2">
        <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-600/15 border border-blue-500/30 text-blue-400 shadow-lg shadow-blue-950/50 mb-1">
            <i class="fa-solid fa-shield-halved text-xl"></i>
        </div>
        <h1 class="text-2xl font-bold tracking-tight text-white">
            Data<span class="text-blue-400">Guard</span>
        </h1>
        <p class="text-xs text-slate-400 max-w-xs mx-auto leading-relaxed">
            A Web-Based Data Loss Prevention and Monitoring System
        </p>
    </div>

    <!-- Central Login Card -->
    <div class="p-6 sm:p-7 rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl space-y-5">
        <!-- Card Header -->
        <div class="pb-1 border-b border-slate-800/80">
            <h2 class="text-sm font-semibold text-white">Sign In to Console</h2>
            <p class="text-[11px] text-slate-400 mt-0.5">Enter your credentials to access the DLP monitoring system</p>
        </div>

        <!-- Feedback & Alerts -->
        @include('partials.alerts')

        <!-- Login Form -->
        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf

            <!-- Email Field -->
            <div class="space-y-1.5">
                <label for="email" class="block text-xs font-medium text-slate-300">
                    Email Address <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i class="fa-regular fa-envelope text-xs"></i>
                    </div>
                    <input id="email" type="email" name="email" value="{{ old('email', 'admin@dataguard.corp') }}" required autofocus
                        placeholder="e.g. user@dataguard.corp"
                        class="w-full pl-9 pr-3.5 py-2.5 bg-slate-950 border @error('email') border-rose-500 @else border-slate-700 @enderror rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition">
                </div>
                @error('email')
                    <p class="text-[11px] text-rose-400 flex items-center gap-1 mt-1">
                        <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- Password Field with Show/Hide Toggle -->
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-xs font-medium text-slate-300">
                        Password <span class="text-rose-400">*</span>
                    </label>
                    <button type="button" onclick="toggleForgotPasswordModal(true)" class="text-[11px] text-blue-400 hover:text-blue-300 transition">
                        Forgot Password?
                    </button>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i class="fa-solid fa-lock text-xs"></i>
                    </div>
                    <input id="password" type="password" name="password" value="password" required
                        placeholder="Enter your security password"
                        class="w-full pl-9 pr-10 py-2.5 bg-slate-950 border @error('password') border-rose-500 @else border-slate-700 @enderror rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition font-mono">
                    
                    <!-- Show/Hide Password Toggle Button -->
                    <button type="button" id="togglePasswordBtn" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-200 transition focus:outline-none" title="Show or hide password">
                        <i class="fa-regular fa-eye text-xs" id="passwordEyeIcon"></i>
                    </button>
                </div>
                @error('password')
                    <p class="text-[11px] text-rose-400 flex items-center gap-1 mt-1">
                        <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-0.5">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" name="remember" id="remember" class="w-3.5 h-3.5 rounded bg-slate-950 border-slate-700 text-blue-600 focus:ring-0 focus:ring-offset-0">
                    <span class="text-xs text-slate-400">Remember this session</span>
                </label>
            </div>

            <!-- Sign In / Continue Button -->
            <div class="pt-1">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs transition shadow-lg shadow-blue-950/60 flex items-center justify-center gap-2">
                    <span>Sign In to DataGuard</span>
                    <i class="fa-solid fa-arrow-right text-[11px]"></i>
                </button>
            </div>
        </form>

        <!-- 1-Click Quick Demo Login (Integrated for Presentation) -->
        <div class="pt-4 border-t border-slate-800 space-y-2">
            <div class="flex items-center justify-between text-[11px]">
                <span class="text-slate-500 font-medium">Quick Demo Access</span>
                <span class="text-[10px] text-slate-500">1-click role switch</span>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <a href="{{ route('login.quick', 'admin') }}" class="p-2 rounded-lg bg-slate-950 border border-slate-800 hover:border-blue-500/60 transition text-center group">
                    <div class="text-xs font-medium text-slate-200 group-hover:text-blue-400">Admin</div>
                    <div class="text-[10px] text-slate-500">Full Access</div>
                </a>
                <a href="{{ route('login.quick', 'analyst') }}" class="p-2 rounded-lg bg-slate-950 border border-slate-800 hover:border-blue-500/60 transition text-center group">
                    <div class="text-xs font-medium text-slate-200 group-hover:text-blue-400">Analyst</div>
                    <div class="text-[10px] text-slate-500">Alert Triage</div>
                </a>
                <a href="{{ route('login.quick', 'user') }}" class="p-2 rounded-lg bg-slate-950 border border-slate-800 hover:border-blue-500/60 transition text-center group">
                    <div class="text-xs font-medium text-slate-200 group-hover:text-blue-400">User</div>
                    <div class="text-[10px] text-slate-500">Submit Data</div>
                </a>
            </div>
        </div>

        <!-- Register Link -->
        <div class="pt-3 border-t border-slate-800 text-center">
            <p class="text-xs text-slate-400">
                Need an employee account?
                <a href="{{ route('register') }}" class="font-semibold text-blue-400 hover:text-blue-300 transition ml-1">
                    Register here
                </a>
            </p>
        </div>
    </div>

    <!-- Security Footer Notice -->
    <div class="text-center space-y-1">
        <p class="text-[11px] text-slate-400 flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-lock text-[10px] text-blue-400"></i>
            <span>Authorized Corporate Personnel Only • Strict Rule-Based DLP</span>
        </p>
    </div>
</div>

<!-- Forgot Password Help Modal -->
<div id="forgotPasswordModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="max-w-sm w-full p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl space-y-3.5">
        <div class="flex items-center justify-between border-b border-slate-800 pb-2">
            <div class="flex items-center gap-2 text-white text-xs font-semibold">
                <i class="fa-solid fa-key text-blue-400"></i>
                <span>Password Recovery</span>
            </div>
            <button type="button" onclick="toggleForgotPasswordModal(false)" class="text-slate-400 hover:text-white transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
        <div class="space-y-2 text-xs text-slate-300 leading-relaxed">
            <p>
                In compliance with organizational Data Loss Prevention policies, user credentials are centrally managed by IT Security.
            </p>
            <div class="p-2.5 rounded-lg bg-slate-950 border border-slate-800 text-[11px] text-slate-400 space-y-1">
                <div class="text-slate-200 font-medium">To reset your credentials:</div>
                <div>&bull; Contact your System Administrator</div>
                <div>&bull; Email: <span class="font-mono text-blue-400">admin@dataguard.corp</span></div>
                <div>&bull; Default demo password: <span class="font-mono text-amber-400">password</span></div>
            </div>
        </div>
        <button type="button" onclick="toggleForgotPasswordModal(false)" class="w-full py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-medium transition">
            Close
        </button>
    </div>
</div>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('passwordEyeIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }

    function toggleForgotPasswordModal(show) {
        const modal = document.getElementById('forgotPasswordModal');
        if (show) {
            modal.classList.remove('hidden');
        } else {
            modal.classList.add('hidden');
        }
    }
</script>
@endsection