@extends('layouts.app')

@section('title', 'Edit User: ' . $user->name)

@section('content')
<div class="max-w-xl mx-auto space-y-5">
    <div class="flex items-center justify-between pb-2 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Edit User</h1>
            <p class="text-xs text-slate-400 mt-0.5">Update user credentials and role</p>
        </div>
        <a href="{{ route('users.index') }}" class="text-xs text-slate-400 hover:text-white transition">Cancel</a>
    </div>

    <div class="p-6 rounded-xl bg-slate-900 border border-slate-800">
        <form method="POST" action="{{ route('users.update', $user->id) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block text-xs font-medium text-slate-300 mb-1">Full Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div>
                <label for="email" class="block text-xs font-medium text-slate-300 mb-1">Email Address *</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div>
                <label for="password" class="block text-xs font-medium text-slate-300 mb-1">New Password (Leave blank to keep current)</label>
                <input type="password" name="password" id="password"
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="role" class="block text-xs font-medium text-slate-300 mb-1">System Role *</label>
                    <select name="role" id="role" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Standard User</option>
                        <option value="analyst" {{ $user->role === 'analyst' ? 'selected' : '' }}>Security Analyst</option>
                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrator</option>
                    </select>
                </div>
                <div>
                    <label for="department" class="block text-xs font-medium text-slate-300 mb-1">Department</label>
                    <input type="text" name="department" id="department" value="{{ old('department', $user->department) }}"
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                <a href="{{ route('users.index') }}" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-xs font-medium">Cancel</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs">Update User</button>
            </div>
        </form>
    </div>
</div>
@endsection