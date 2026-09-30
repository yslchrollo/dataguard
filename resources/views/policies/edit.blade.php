@extends('layouts.app')

@section('title', 'Edit Security Policy')

@section('content')
<div class="max-w-xl mx-auto space-y-5">
    <div class="flex items-center justify-between pb-2 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Edit Policy: {{ $policy->policy_code }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">Update DLP policy definition</p>
        </div>
        <a href="{{ route('policies.index') }}" class="text-xs text-slate-400 hover:text-white transition">Cancel</a>
    </div>

    <div class="p-6 rounded-xl bg-slate-900 border border-slate-800">
        <form method="POST" action="{{ route('policies.update', $policy->id) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="policy_code" class="block text-xs font-medium text-slate-300 mb-1">Policy Code *</label>
                    <input type="text" name="policy_code" id="policy_code" value="{{ old('policy_code', $policy->policy_code) }}" required
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs font-mono text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
                <div>
                    <label for="name" class="block text-xs font-medium text-slate-300 mb-1">Policy Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $policy->name) }}" required
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label for="description" class="block text-xs font-medium text-slate-300 mb-1">Description</label>
                <textarea name="description" id="description" rows="2"
                    class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">{{ old('description', $policy->description) }}</textarea>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label for="policy_type" class="block text-xs font-medium text-slate-300 mb-1">Policy Type *</label>
                    <select name="policy_type" id="policy_type" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="sensitive_data_rule" {{ $policy->policy_type === 'sensitive_data_rule' ? 'selected' : '' }}>Sensitive Data</option>
                        <option value="destination_check" {{ $policy->policy_type === 'destination_check' ? 'selected' : '' }}>Destination Check</option>
                        <option value="file_type_restriction" {{ $policy->policy_type === 'file_type_restriction' ? 'selected' : '' }}>File Restriction</option>
                        <option value="file_size_limit" {{ $policy->policy_type === 'file_size_limit' ? 'selected' : '' }}>Size Limit</option>
                        <option value="rate_limit" {{ $policy->policy_type === 'rate_limit' ? 'selected' : '' }}>Rate Limit</option>
                    </select>
                </div>
                <div>
                    <label for="action_on_violation" class="block text-xs font-medium text-slate-300 mb-1">Action *</label>
                    <select name="action_on_violation" id="action_on_violation" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="block" {{ $policy->action_on_violation === 'block' ? 'selected' : '' }}>Block</option>
                        <option value="flag" {{ $policy->action_on_violation === 'flag' ? 'selected' : '' }}>Flag</option>
                    </select>
                </div>
                <div>
                    <label for="severity" class="block text-xs font-medium text-slate-300 mb-1">Severity *</label>
                    <select name="severity" id="severity" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="critical" {{ $policy->severity === 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="high" {{ $policy->severity === 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ $policy->severity === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ $policy->severity === 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
            </div>

            <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
                <a href="{{ route('policies.index') }}" class="px-4 py-2 rounded-lg bg-slate-800 text-slate-300 text-xs font-medium">Cancel</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs">Update Policy</button>
            </div>
        </form>
    </div>
</div>
@endsection