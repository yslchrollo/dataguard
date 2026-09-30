@extends('layouts.app')

@section('title', 'Security Reports')

@section('content')
<div class="space-y-5">
    <!-- Header with Print Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Security & Compliance Reports</h1>
            <p class="text-xs text-slate-400 mt-0.5">Summary of outbound data transfers, policy violations, and destination risks</p>
        </div>
        <button type="button" onclick="window.print()" class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-medium transition inline-flex items-center gap-1.5 self-start sm:self-auto">
            <i class="fa-solid fa-print text-slate-400"></i>
            <span>Print / Export PDF</span>
        </button>
    </div>

    <!-- Date Range Filter -->
    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400 font-medium">From:</span>
                <input type="date" name="start_date" value="{{ $startDate }}" class="px-2.5 py-1 bg-slate-950 border border-slate-700 rounded-md text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-400 font-medium">To:</span>
                <input type="date" name="end_date" value="{{ $endDate }}" class="px-2.5 py-1 bg-slate-950 border border-slate-700 rounded-md text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-3 py-1 rounded-md bg-blue-600 hover:bg-blue-500 text-white text-xs font-medium transition">
                Apply Filter
            </button>
        </form>
    </div>

    <!-- 4 KPI Metrics -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <span class="text-xs text-slate-400 font-medium">Total Transfers</span>
            <div class="text-xl font-bold text-white mt-1">{{ $totalTransfers }}</div>
        </div>
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <span class="text-xs text-emerald-400 font-medium">Allowed Transfers</span>
            <div class="text-xl font-bold text-emerald-400 mt-1">{{ $allowedTransfers }}</div>
        </div>
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <span class="text-xs text-rose-400 font-medium">Blocked Violations</span>
            <div class="text-xl font-bold text-rose-400 mt-1">{{ $blockedTransfers }}</div>
        </div>
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <span class="text-xs text-amber-400 font-medium">Flagged & Alerts</span>
            <div class="text-xl font-bold text-amber-400 mt-1">{{ $flaggedTransfers }}</div>
        </div>
    </div>

    <!-- Dual Analysis Tables: Top Destinations & Top Users -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Top Destinations -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-300">
                Top Transfer Destinations
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-800 font-medium">
                            <th class="pb-2">Destination</th>
                            <th class="pb-2">Type</th>
                            <th class="pb-2 text-center">Requests</th>
                            <th class="pb-2 text-right">Blocked</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        @forelse($topDestinations as $dest)
                        <tr>
                            <td class="py-2 font-mono text-slate-200 truncate max-w-[170px]">{{ $dest->destination }}</td>
                            <td class="py-2">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase
                                    {{ $dest->destination_type === 'internal' ? 'bg-emerald-500/15 text-emerald-400' : 'bg-rose-500/15 text-rose-400' }}">
                                    {{ $dest->destination_type === 'internal' ? 'Internal' : 'External' }}
                                </span>
                            </td>
                            <td class="py-2 text-center font-mono text-slate-200">{{ $dest->count }}</td>
                            <td class="py-2 text-right font-mono font-bold text-rose-400">{{ $dest->blocked_count }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-4 text-center text-slate-500">No destination activity recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Users by Submissions -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-300">
                Transfer Activity by Employee
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-800 font-medium">
                            <th class="pb-2">Employee</th>
                            <th class="pb-2">Department</th>
                            <th class="pb-2 text-center">Transfers</th>
                            <th class="pb-2 text-right">Violations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        @forelse($topUsers as $tu)
                        <tr>
                            <td class="py-2 font-medium text-slate-200">{{ $tu->user->name ?? 'Unknown' }}</td>
                            <td class="py-2 text-slate-400">{{ $tu->user->department ?? 'General' }}</td>
                            <td class="py-2 text-center font-mono text-slate-200">{{ $tu->transfer_count }}</td>
                            <td class="py-2 text-right font-mono font-bold text-rose-400">{{ $tu->blocked_count }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="py-4 text-center text-slate-500">No user data found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection