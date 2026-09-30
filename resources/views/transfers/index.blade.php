@extends('layouts.app')

@section('title', 'Transfer Activity Logs')

@section('content')
<div class="space-y-5">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Transfer Activity Logs</h1>
            <p class="text-xs text-slate-400 mt-0.5">Audit trail of all inspected outbound data transfers</p>
        </div>
        <a href="{{ route('transfers.create') }}" class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs transition inline-flex items-center gap-1.5 self-start sm:self-auto">
            <i class="fa-solid fa-plus text-[10px]"></i>
            <span>New Transfer</span>
        </a>
    </div>

    <!-- Filter & Search Controls -->
    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3">
        <!-- Decision Filter Tabs -->
        <div class="flex items-center gap-1 overflow-x-auto w-full sm:w-auto">
            <a href="{{ route('transfers.index', request()->except('decision', 'page')) }}"
               class="px-2.5 py-1 rounded-md text-xs font-medium transition {{ !request()->filled('decision') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                All
            </a>
            <a href="{{ route('transfers.index', array_merge(request()->except('page'), ['decision' => 'allowed'])) }}"
               class="px-2.5 py-1 rounded-md text-xs font-medium transition {{ request('decision') === 'allowed' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                Allowed
            </a>
            <a href="{{ route('transfers.index', array_merge(request()->except('page'), ['decision' => 'blocked'])) }}"
               class="px-2.5 py-1 rounded-md text-xs font-medium transition {{ request('decision') === 'blocked' ? 'bg-rose-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                Blocked
            </a>
            <a href="{{ route('transfers.index', array_merge(request()->except('page'), ['decision' => 'flagged'])) }}"
               class="px-2.5 py-1 rounded-md text-xs font-medium transition {{ request('decision') === 'flagged' ? 'bg-amber-600 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                Flagged
            </a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('transfers.index') }}" class="w-full sm:w-64 flex items-center gap-2">
            @if(request()->filled('decision'))
                <input type="hidden" name="decision" value="{{ request('decision') }}">
            @endif
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search file, user, ID..."
                    class="w-full px-3 py-1.5 bg-slate-950 border border-slate-700 rounded-md text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <button type="submit" class="px-2.5 py-1.5 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium">
                Search
            </button>
        </form>
    </div>

    <!-- Main Table -->
    <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 font-medium">
                        <th class="pb-2.5">Transfer ID</th>
                        <th class="pb-2.5">User</th>
                        <th class="pb-2.5">File</th>
                        <th class="pb-2.5">Destination</th>
                        <th class="pb-2.5">Decision</th>
                        <th class="pb-2.5">Risk Score</th>
                        <th class="pb-2.5">Date</th>
                        <th class="pb-2.5 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @forelse($transfers as $trf)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="py-2.5 pr-2 font-mono text-slate-300 font-medium">
                            {{ $trf->transfer_uuid }}
                        </td>
                        <td class="py-2.5 pr-2">
                            <span class="text-slate-200 font-medium">{{ $trf->user->name }}</span>
                        </td>
                        <td class="py-2.5 pr-2">
                            <div class="text-slate-200 truncate max-w-[160px]" title="{{ $trf->file_name }}">
                                {{ $trf->file_name }}
                            </div>
                        </td>
                        <td class="py-2.5 pr-2 font-mono text-slate-400 truncate max-w-[150px]" title="{{ $trf->destination }}">
                            {{ $trf->destination }}
                        </td>
                        <td class="py-2.5 pr-2">
                            @if($trf->decision === 'allowed')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/15 text-emerald-400">ALLOWED</span>
                            @elseif($trf->decision === 'blocked')
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/15 text-rose-400">BLOCKED</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/15 text-amber-400">FLAGGED</span>
                            @endif
                        </td>
                        <td class="py-2.5 pr-2 font-mono">
                            <span class="{{ $trf->risk_score >= 60 ? 'text-rose-400' : ($trf->risk_score >= 20 ? 'text-amber-400' : 'text-emerald-400') }}">
                                {{ $trf->risk_score }}/100
                            </span>
                        </td>
                        <td class="py-2.5 pr-2 text-slate-400 font-mono text-[11px]">
                            {{ $trf->created_at->format('M d, H:i') }}
                        </td>
                        <td class="py-2.5 text-right">
                            <a href="{{ route('transfers.show', $trf->transfer_uuid) }}" class="text-blue-400 hover:text-blue-300 font-medium">
                                Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-6 text-center text-slate-500">
                            No transfer activity records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3 pt-3 border-t border-slate-800">
            {{ $transfers->links() }}
        </div>
    </div>
</div>
@endsection