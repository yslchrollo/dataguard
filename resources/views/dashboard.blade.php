@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Security Dashboard</h1>
            <p class="text-xs text-slate-400 mt-0.5">Outbound transfer monitoring and DLP policy enforcement overview</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('transfers.create') }}" class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs transition inline-flex items-center gap-1.5">
                <i class="fa-solid fa-plus text-[10px]"></i>
                <span>Submit Transfer</span>
            </a>
        </div>
    </div>

    <!-- 4 Primary Metric Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Transfers -->
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <span class="text-xs font-medium text-slate-400">Total Transfers</span>
            <div class="text-2xl font-bold text-white mt-1">{{ number_format($totalTransfers) }}</div>
            <p class="text-[11px] text-slate-500 mt-1">Total requests inspected</p>
        </div>

        <!-- Allowed Transfers -->
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <span class="text-xs font-medium text-emerald-400">Allowed</span>
            <div class="text-2xl font-bold text-emerald-400 mt-1">{{ number_format($allowedCount) }}</div>
            <p class="text-[11px] text-slate-500 mt-1">Compliant transfers</p>
        </div>

        <!-- Blocked Transfers -->
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <span class="text-xs font-medium text-rose-400">Blocked</span>
            <div class="text-2xl font-bold text-rose-400 mt-1">{{ number_format($blockedCount) }}</div>
            <p class="text-[11px] text-slate-500 mt-1">Policy violations stopped</p>
        </div>

        <!-- Flagged / Active Alerts -->
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800">
            <span class="text-xs font-medium text-amber-400">Flagged / Alerts</span>
            <div class="text-2xl font-bold text-amber-400 mt-1">{{ number_format($flaggedCount) }}</div>
            <p class="text-[11px] text-amber-400/80 mt-1">{{ $activeAlerts }} Active Security Alerts</p>
        </div>
    </div>

    <!-- 7-Day Transfer & Policy Trend Chart -->
    <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-white">Transfer Activity & Verdict Trends</h3>
                <p class="text-xs text-slate-400">7-Day summary of Allowed, Blocked, and Flagged transfers</p>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <span class="inline-flex items-center gap-1.5 text-emerald-400"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> Allowed</span>
                <span class="inline-flex items-center gap-1.5 text-rose-400"><span class="w-2 h-2 rounded-full bg-rose-500"></span> Blocked</span>
                <span class="inline-flex items-center gap-1.5 text-amber-400"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Flagged</span>
            </div>
        </div>
        <div class="h-56 relative">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    <!-- Dual Tables: Recent Transfers & Recent Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Transfer Activity -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-white">Recent Transfer Activity</h3>
                <a href="{{ route('transfers.index') }}" class="text-xs text-blue-400 hover:text-blue-300">View All &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 border-b border-slate-800 font-medium">
                            <th class="pb-2">File</th>
                            <th class="pb-2">Destination</th>
                            <th class="pb-2">Decision</th>
                            <th class="pb-2 text-right">Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        @forelse($recentTransfers as $trf)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-2.5 pr-2">
                                <div class="font-medium text-slate-200 truncate max-w-[140px]" title="{{ $trf->file_name }}">
                                    {{ $trf->file_name }}
                                </div>
                                <div class="text-[10px] text-slate-500">{{ $trf->user->name }}</div>
                            </td>
                            <td class="py-2.5 pr-2">
                                <span class="font-mono text-slate-300 truncate max-w-[130px] block" title="{{ $trf->destination }}">
                                    {{ $trf->destination }}
                                </span>
                                <span class="text-[10px] {{ $trf->destination_type === 'internal' ? 'text-emerald-400' : 'text-slate-400' }}">
                                    {{ $trf->destination_type === 'internal' ? 'Internal' : 'External' }}
                                </span>
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
                            <td class="py-2.5 text-right">
                                <a href="{{ route('transfers.show', $trf->transfer_uuid) }}" class="text-blue-400 hover:text-blue-300 font-medium">
                                    View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-6 text-center text-slate-500">No transfers recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Security Alerts -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-white">Active Security Alerts</h3>
                <a href="{{ route('alerts.index') }}" class="text-xs text-blue-400 hover:text-blue-300">View All &rarr;</a>
            </div>

            <div class="space-y-2.5">
                @forelse($recentAlerts as $alert)
                <div class="p-3 rounded-lg bg-slate-950/60 border border-slate-800/80 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5 overflow-hidden">
                        <span class="w-2 h-2 rounded-full shrink-0
                            {{ $alert->severity === 'critical' ? 'bg-rose-500' : ($alert->severity === 'high' ? 'bg-orange-500' : 'bg-amber-500') }}"></span>
                        <div class="truncate">
                            <div class="text-xs font-medium text-slate-200 truncate">{{ $alert->title }}</div>
                            <div class="text-[10px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                <span class="font-mono">{{ $alert->alert_uuid }}</span>
                                <span>&bull;</span>
                                <span class="capitalize">{{ $alert->severity }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                            {{ $alert->status === 'resolved' ? 'bg-emerald-500/15 text-emerald-400' : ($alert->status === 'under_review' ? 'bg-blue-500/15 text-blue-400' : 'bg-rose-500/15 text-rose-400') }}">
                            {{ str_replace('_', ' ', $alert->status) }}
                        </span>
                        <a href="{{ route('alerts.show', $alert->id) }}" class="p-1 rounded hover:bg-slate-800 text-slate-400 hover:text-white" title="Investigate">
                            <i class="fa-solid fa-chevron-right text-[10px]"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="py-8 text-center text-slate-500 text-xs">No active security alerts.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($dates) !!},
            datasets: [
                {
                    label: 'Allowed',
                    data: {!! json_encode($trendAllowed) !!},
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.08)',
                    tension: 0.3,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 3
                },
                {
                    label: 'Blocked',
                    data: {!! json_encode($trendBlocked) !!},
                    borderColor: '#f43f5e',
                    backgroundColor: 'rgba(244, 63, 94, 0.08)',
                    tension: 0.3,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 3
                },
                {
                    label: 'Flagged',
                    data: {!! json_encode($trendFlagged) !!},
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, 0.08)',
                    tension: 0.3,
                    fill: true,
                    borderWidth: 2,
                    pointRadius: 3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { color: '#1e293b' },
                    ticks: { color: '#64748b', font: { size: 10 } }
                },
                y: {
                    grid: { color: '#1e293b' },
                    ticks: { color: '#64748b', font: { size: 10 }, stepSize: 1 },
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush