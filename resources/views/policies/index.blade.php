@extends('layouts.app')

@section('title', 'Security Policies')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Security Policies</h1>
            <p class="text-xs text-slate-400 mt-0.5">DLP transfer rules, destination restrictions, and automated enforcement actions</p>
        </div>
        <a href="{{ route('policies.create') }}" class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs transition inline-flex items-center gap-1.5 self-start sm:self-auto">
            <i class="fa-solid fa-plus text-[10px]"></i>
            <span>Add Policy</span>
        </a>
    </div>

    <!-- Policies Table -->
    <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 font-medium">
                        <th class="pb-2.5">Code</th>
                        <th class="pb-2.5">Policy Name</th>
                        <th class="pb-2.5">Type</th>
                        <th class="pb-2.5">Action</th>
                        <th class="pb-2.5">Severity</th>
                        <th class="pb-2.5">Status</th>
                        <th class="pb-2.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @forelse($policies as $policy)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-2.5 pr-2 font-mono text-blue-400 font-medium">{{ $policy->policy_code }}</td>
                        <td class="py-2.5 pr-2">
                            <div class="font-medium text-slate-200">{{ $policy->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ $policy->description }}</div>
                        </td>
                        <td class="py-2.5 pr-2 text-slate-300 font-mono text-[11px]">
                            {{ str_replace('_', ' ', $policy->policy_type) }}
                        </td>
                        <td class="py-2.5 pr-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $policy->action_on_violation === 'block' ? 'bg-rose-500/15 text-rose-400' : 'bg-amber-500/15 text-amber-400' }}">
                                {{ $policy->action_on_violation }}
                            </span>
                        </td>
                        <td class="py-2.5 pr-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $policy->severity === 'critical' ? 'text-rose-400' : ($policy->severity === 'high' ? 'text-orange-400' : 'text-amber-400') }}">
                                {{ $policy->severity }}
                            </span>
                        </td>
                        <td class="py-2.5 pr-2">
                            <form action="{{ route('policies.toggle', $policy->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase transition
                                    {{ $policy->is_active ? 'bg-emerald-500/15 text-emerald-400' : 'bg-slate-800 text-slate-500' }}">
                                    {{ $policy->is_active ? 'Enabled' : 'Disabled' }}
                                </button>
                            </form>
                        </td>
                        <td class="py-2.5 text-right space-x-2">
                            <a href="{{ route('policies.edit', $policy->id) }}" class="text-blue-400 hover:text-blue-300 font-medium">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="py-6 text-center text-slate-500">No security policies configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection