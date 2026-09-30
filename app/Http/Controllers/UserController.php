<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $users = $query->paginate(10)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['admin', 'analyst', 'user'])],
            'department' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        $validated['status'] = !empty($validated['status']) ? $validated['status'] : 'active';
        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        ActivityLog::record(
            'user_created',
            'UserManagement',
            "Administrator created new user account '{$user->name}' with role '{$user->role}'.",
            ['user_id' => $user->id, 'email' => $user->email, 'role' => $user->role]
        );

        return redirect()->route('users.index')->with('success', "User '{$user->name}' successfully created.");
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::in(['admin', 'analyst', 'user'])],
            'department' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', Rule::in(['active', 'inactive'])],
        ]);

        if (empty($validated['status'])) {
            unset($validated['status']);
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        ActivityLog::record(
            'user_updated',
            'UserManagement',
            "Administrator updated account details for '{$user->name}'.",
            ['user_id' => $user->id, 'updated_role' => $user->role, 'status' => $user->status]
        );

        return redirect()->route('users.index')->with('success', "User '{$user->name}' updated successfully.");
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own logged-in account.');
        }

        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->update(['status' => $newStatus]);

        ActivityLog::record(
            'user_status_toggled',
            'UserManagement',
            "User '{$user->name}' status changed to {$newStatus}.",
            ['user_id' => $user->id, 'new_status' => $newStatus]
        );

        return back()->with('success', "User '{$user->name}' is now {$newStatus}.");
    }
}
