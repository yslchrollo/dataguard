@extends('layouts.app')

@section('title', 'Security Alerts')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Security Alerts</h1>
            <p class="text-xs text-slate-400 mt-0.5">Policy violations and suspicious transfer incidents requiring triage</p>
        </div>
        <div class="text-xs text-slate-400 font-mono">
            Total: {{ $alerts->total() }} Alert(s)
        </div>
    </div>

    <!-- Filters -->
    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-1 overflow-x-auto w-full sm:w-auto">
            <a href="{{ route('alerts.index', request()->except('status', 'page')) }}"
               class="px-2.5 py-1 rounded-md text-xs font-medium transition {{ !request()->filled('status') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                All
            </a>
            <a href="{{ route('alerts.index', array_merge(request()->except('page'), ['status' => 'open'])) }}"
               class="px-2.5 py-1 rounded-md text-xs font-medium transition {{ request('status') === 'open' ? 'bg-rose-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                Open
            </a>
            <a href="{{ route('alerts.index', array_merge(request()->except('page'), ['status' => 'under_review'])) }}"
               class="px-2.5 py-1 rounded-md text-xs font-medium transition {{ request('status') === 'under_review' ? 'bg-blue-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                Under Review
            </a>
            <a href="{{ route('alerts.index', array_merge(request()->except('page'), ['status' => 'resolved'])) }}"
               class="px-2.5 py-1 rounded-md text-xs font-medium transition {{ request('status') === 'resolved' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                Resolved
            </a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('alerts.index') }}" class="w-full sm:w-64 flex items-center gap-2">
            @if(request()->filled('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search alert ID, title..."
                    class="w-full px-3 py-1.5 bg-slate-950 border border-slate-700 rounded-md text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-2.5 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium">
                Search
            </button>
        </form>
    </div>

    <!-- Alert Table -->
    <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 font-medium">
                        <th class="pb-2.5">Alert ID</th>
                        <th class="pb-2.5">User</th>
                        <th class="pb-2.5">Alert Title</th>
                        <th class="pb-2.5">Severity</th>
                        <th class="pb-2.5">Status</th>
                        <th class="pb-2.5">Date</th>
                        <th class="pb-2.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @forelse($alerts as $alert)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-2.5 pr-2 font-mono text-rose-400 font-medium">
                            {{ $alert->alert_uuid }}
                        </td>
                        <td class="py-2.5 pr-2">
                            <span class="text-slate-200 font-medium">{{ $alert->user->name }}</span>
                            <div class="text-[10px] text-slate-500">{{ $alert->user->department ?? 'Staff' }}</div>
                        </td>
                        <td class="py-2.5 pr-2">
                            <div class="text-slate-200 font-medium truncate max-w-[220px]" title="{{ $alert->title }}">
                                {{ $alert->title }}
                            </div>
                            <div class="text-[10px] text-slate-500">{{ $alert->alert_type }}</div>
                        </td>
                        <td class="py-2.5 pr-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $alert->severity === 'critical' ? 'bg-rose-500/15 text-rose-400' : ($alert->severity === 'high' ? 'bg-orange-500/15 text-orange-400' : 'bg-amber-500/15 text-amber-400') }}">
                                {{ $alert->severity }}
                            </span>
                        </td>
                        <td class="py-2.5 pr-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $alert->status === 'resolved' ? 'bg-emerald-500/15 text-emerald-400' : ($alert->status === 'under_review' ? 'bg-blue-500/15 text-blue-400' : 'bg-rose-500/15 text-rose-400') }}">
                                {{ str_replace('_', ' ', $alert->status) }}
                            </span>
                        </td>
                        <td class="py-2.5 pr-2 text-slate-400 font-mono text-[11px]">
                            {{ $alert->created_at->format('M d, H:i') }}
                        </td>
                        <td class="py-2.5 text-right">
                            <a href="{{ route('alerts.show', $alert->id) }}" class="text-blue-400 hover:text-blue-300 font-medium">
                                Investigate
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-6 text-center text-slate-500">
                            No security alerts found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 pt-3 border-t border-slate-800">
            {{ $alerts->links() }}
        </div>
    </div>
</div>
@endsection