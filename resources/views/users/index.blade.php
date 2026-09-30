@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">User Management</h1>
            <p class="text-xs text-slate-400 mt-0.5">Manage system accounts, access roles, and permissions</p>
        </div>
        <a href="{{ route('users.create') }}" class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs transition inline-flex items-center gap-1.5 self-start sm:self-auto">
            <i class="fa-solid fa-plus text-[10px]"></i>
            <span>Add User</span>
        </a>
    </div>

    <!-- Users Table -->
    <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 font-medium">
                        <th class="pb-2.5">User</th>
                        <th class="pb-2.5">Role</th>
                        <th class="pb-2.5">Department</th>
                        <th class="pb-2.5">Status</th>
                        <th class="pb-2.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @forelse($users as $user)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-2.5 pr-2">
                            <div class="font-medium text-slate-200">{{ $user->name }}</div>
                            <div class="text-[10px] text-slate-500 font-mono">{{ $user->email }}</div>
                        </td>
                        <td class="py-2.5 pr-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $user->role === 'admin' ? 'bg-amber-500/15 text-amber-400' : ($user->role === 'analyst' ? 'bg-blue-500/15 text-blue-400' : 'bg-slate-800 text-slate-300') }}">
                                {{ $user->role === 'admin' ? 'Administrator' : ($user->role === 'analyst' ? 'Security Analyst' : 'Standard User') }}
                            </span>
                        </td>
                        <td class="py-2.5 pr-2 text-slate-300">
                            {{ $user->department ?? 'General' }}
                        </td>
                        <td class="py-2.5 pr-2">
                            <form action="{{ route('users.toggle', $user->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase transition
                                    {{ $user->isActive() ? 'bg-emerald-500/15 text-emerald-400 hover:bg-rose-950/40 hover:text-rose-400' : 'bg-slate-800 text-slate-500 hover:bg-emerald-950/40 hover:text-emerald-400' }}">
                                    {{ $user->status }}
                                </button>
                            </form>
                        </td>
                        <td class="py-2.5 text-right space-x-2">
                            <a href="{{ route('users.edit', $user->id) }}" class="text-blue-400 hover:text-blue-300 font-medium">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-6 text-center text-slate-500">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 pt-3 border-t border-slate-800">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection