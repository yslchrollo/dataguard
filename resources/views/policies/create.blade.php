@extends('layouts.app')

@section('title', 'Add Security Policy')

@section('content')
<div class="max-w-xl mx-auto space-y-5">
    <div class="flex items-center justify-between pb-2 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Create Security Policy</h1>
            <p class="text-xs text-slate-400 mt-0.5">Configure DLP detection rules and actions</p>
        </div>
        <a href="{{ route('policies.index') }}" class="text-xs text-slate-400 hover:text-white transition">Cancel</a>
    </div>

    <div class="p-6 rounded-xl bg-slate-900 border border-slate-800">
        <form method="POST" action="{{ route('policies.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="policy_code" class="block text-xs font-medium text-slate-300 mb-1">Policy Code *</label>
                    <input type="text" name="policy_code" id="policy_code" value="{{ old('policy_code') }}" required placeholder="e.g. POL-DLP-006"
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs font-mono text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label for="name" class="block text-xs font-medium text-slate-300 mb-1">Policy Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="e.g. Cloud Storage Egress Block"
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-medium text-slate-300 mb-1">Description</label>
                <textarea name="description" id="description" rows="2" placeholder="Explain the security rule..."
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label for="policy_type" class="block text-xs font-medium text-slate-300 mb-1">Policy Type *</label>
                    <select name="policy_type" id="policy_type" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="sensitive_data_rule">Sensitive Data</option>
                        <option value="destination_check">Destination Check</option>
                        <option value="file_type_restriction">File Restriction</option>
                        <option value="file_size_limit">Size Limit</option>
                        <option value="rate_limit">Rate Limit</option>
                    </select>
                </div>
                <div>
                    <label for="action_on_violation" class="block text-xs font-medium text-slate-300 mb-1">Action *</label>
                    <select name="action_on_violation" id="action_on_violation" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="block">Block</option>
                        <option value="flag">Flag</option>
                    </select>
                </div>
                <div>
                    <label for="severity" class="block text-xs font-medium text-slate-300 mb-1">Severity *</label>
                    <select name="severity" id="severity" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="critical">Critical</option>
                        <option value="high" selected>High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                <a href="{{ route('policies.index') }}" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-xs font-medium">Cancel</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs">Save Policy</button>
            </div>
        </form>
    </div>
</div>
@endsection