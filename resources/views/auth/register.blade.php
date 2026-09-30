@extends('layouts.guest')

@section('title', 'Register Employee Account')

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

    <!-- Central Register Card -->
    <div class="p-6 sm:p-7 rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl space-y-5">
        <!-- Card Header -->
        <div class="pb-1 border-b border-slate-800/80">
            <h2 class="text-sm font-semibold text-white">Create Employee Account</h2>
            <p class="text-[11px] text-slate-400 mt-0.5">Register for access to submit and monitor data transfers</p>
        </div>

        <!-- Feedback & Alerts -->
        @include('partials.alerts')

        <!-- Register Form -->
        <form method="POST" action="{{ route('register.post') }}" class="space-y-4">
            @csrf

            <!-- Full Name Field -->
            <div class="space-y-1.5">
                <label for="name" class="block text-xs font-medium text-slate-300">
                    Full Name <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i class="fa-regular fa-user text-xs"></i>
                    </div>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                        placeholder="e.g. Juan Dela Cruz"
                        class="w-full pl-9 pr-3.5 py-2.5 bg-slate-950 border @error('name') border-rose-500 @else border-slate-700 @enderror rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition">
                </div>
                @error('name')
                    <p class="text-[11px] text-rose-400 flex items-center gap-1 mt-1">
                        <i class="fa-solid fa-circle-exclamation text-[10px]"></i>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- Corporate Email Field -->
            <div class="space-y-1.5">
                <label for="email" class="block text-xs font-medium text-slate-300">
                    Corporate Email Address <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i class="fa-regular fa-envelope text-xs"></i>
                    </div>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                        placeholder="e.g. employee@company.com"
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
                <label for="password" class="block text-xs font-medium text-slate-300">
                    Password <span class="text-rose-400">*</span>
                    <span class="text-[10px] text-slate-500 font-normal ml-1">(min. 8 characters)</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i class="fa-solid fa-lock text-xs"></i>
                    </div>
                    <input id="password" type="password" name="password" required
                        placeholder="Create a strong security password"
                        class="w-full pl-9 pr-10 py-2.5 bg-slate-950 border @error('password') border-rose-500 @else border-slate-700 @enderror rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition font-mono">
                    
                    <button type="button" onclick="togglePasswordVisibility('password', 'passwordEyeIcon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-200 transition focus:outline-none" title="Show or hide password">
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

            <!-- Confirm Password Field with Show/Hide Toggle -->
            <div class="space-y-1.5">
                <label for="password_confirmation" class="block text-xs font-medium text-slate-300">
                    Confirm Password <span class="text-rose-400">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                        <i class="fa-solid fa-lock-check text-xs"></i>
                    </div>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                        placeholder="Re-enter your password to confirm"
                        class="w-full pl-9 pr-10 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500 transition font-mono">
                    
                    <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'confirmEyeIcon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-200 transition focus:outline-none" title="Show or hide password">
                        <i class="fa-regular fa-eye text-xs" id="confirmEyeIcon"></i>
                    </button>
                </div>
            </div>

            <!-- Role Enforcement Notice -->
            <div class="p-3 rounded-xl bg-slate-950 border border-slate-800 space-y-1">
                <div class="flex items-center gap-2 text-[11px] font-medium text-slate-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                    <span>Assigned Role: <span class="text-blue-400 font-semibold uppercase">Regular Employee / User</span></span>
                </div>
                <p class="text-[10px] text-slate-400 leading-relaxed">
                    New accounts are registered with transfer submission access. Administrator and Analyst security privileges must be provisioned by IT Security.
                </p>
            </div>

            <!-- Register Button -->
            <div class="pt-1">
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs transition shadow-lg shadow-blue-950/60 flex items-center justify-center gap-2">
                    <span>Create Account</span>
                    <i class="fa-solid fa-user-plus text-[11px]"></i>
                </button>
            </div>
        </form>

        <!-- Back to Login Navigation -->
        <div class="pt-3 border-t border-slate-800 text-center">
            <p class="text-xs text-slate-400">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-blue-400 hover:text-blue-300 transition ml-1">
                    Sign In to Console
                </a>
            </p>
        </div>
    </div>

    <!-- Security Notice -->
    <div class="text-center space-y-1">
        <p class="text-[11px] text-slate-400 flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-lock text-[10px] text-blue-400"></i>
            <span>Role-Based Access Control • Data Loss Prevention Policy Enforced</span>
        </p>
    </div>
</div>

<script>
    function togglePasswordVisibility(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endsection
