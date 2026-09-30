@extends('layouts.app')

@section('title', 'Detection Result: ' . $transfer->transfer_uuid)

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Breadcrumb -->
    <div class="flex items-center justify-between">
        <a href="{{ route('transfers.index') }}" class="text-xs text-slate-400 hover:text-white transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
            <span>Back to Transfer Logs</span>
        </a>
        <span class="text-xs font-mono text-slate-400">ID: {{ $transfer->transfer_uuid }}</span>
    </div>

    <!-- Primary Decision Banner -->
    @if($transfer->decision === 'allowed')
    <div class="p-5 rounded-xl bg-slate-900 border border-emerald-500/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400">ALLOWED (CLEAN / SAFE)</span>
                    <span class="text-xs text-slate-400">{{ $transfer->scanned_at->format('M d, Y H:i:s') }}</span>
                </div>
                <h1 class="text-lg font-bold text-white mt-0.5">Transfer Approved (Clean / Safe)</h1>
                <p class="text-xs text-slate-300 mt-0.5">{{ $transfer->decision_reason }}</p>
            </div>
        </div>
        <div class="text-right shrink-0">
            <span class="text-[10px] text-slate-400 block uppercase">Risk Score</span>
            <span class="text-xl font-bold text-emerald-400 font-mono">{{ $transfer->risk_score }}<span class="text-xs text-slate-500">/100</span></span>
        </div>
    </div>
    @elseif($transfer->decision === 'blocked')
    <div class="p-5 rounded-xl bg-slate-900 border border-rose-500/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-lg bg-rose-500/15 text-rose-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-ban"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-400">BLOCKED (THREAT)</span>
                    <span class="text-xs text-slate-400">{{ $transfer->scanned_at->format('M d, Y H:i:s') }}</span>
                </div>
                <h1 class="text-lg font-bold text-white mt-0.5">Transfer Blocked by Policy</h1>
                <p class="text-xs text-rose-200/90 mt-0.5">{{ $transfer->decision_reason }}</p>
            </div>
        </div>
        <div class="text-right shrink-0">
            <span class="text-[10px] text-slate-400 block uppercase">Risk Score</span>
            <span class="text-xl font-bold text-rose-400 font-mono">{{ $transfer->risk_score }}<span class="text-xs text-slate-500">/100</span></span>
        </div>
    </div>
    @else
    <div class="p-5 rounded-xl bg-slate-900 border border-amber-500/40 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400">FLAGGED (SUSPICIOUS)</span>
                    <span class="text-xs text-slate-400">{{ $transfer->scanned_at->format('M d, Y H:i:s') }}</span>
                </div>
                <h1 class="text-lg font-bold text-white mt-0.5">Flagged for Review (Suspicious)</h1>
                <p class="text-xs text-amber-200/90 mt-0.5">{{ $transfer->decision_reason }}</p>
            </div>
        </div>
        <div class="text-right shrink-0">
            <span class="text-[10px] text-slate-400 block uppercase">Risk Score</span>
            <span class="text-xl font-bold text-amber-400 font-mono">{{ $transfer->risk_score }}<span class="text-xs text-slate-500">/100</span></span>
        </div>
    </div>
    @endif

    <!-- Transfer Metadata Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-1.5">
            <span class="text-[11px] font-medium text-slate-400 uppercase">File Details</span>
            <div class="text-xs font-semibold text-white truncate" title="{{ $transfer->file_name }}">{{ $transfer->file_name }}</div>
            <div class="text-[11px] text-slate-400 font-mono">.{{ $transfer->file_extension ?: 'txt' }} &bull; {{ round($transfer->file_size_bytes / 1024, 1) }} KB</div>
        </div>

        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-1.5">
            <span class="text-[11px] font-medium text-slate-400 uppercase">Destination</span>
            <div class="text-xs font-mono text-white truncate" title="{{ $transfer->destination }}">{{ $transfer->destination }}</div>
            <div class="text-[11px] {{ $transfer->destination_type === 'internal' ? 'text-emerald-400' : 'text-slate-400' }}">
                {{ $transfer->destination_type === 'internal' ? 'Approved Domain' : 'External Endpoint' }}
            </div>
        </div>

        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-1.5">
            <span class="text-[11px] font-medium text-slate-400 uppercase">Submitted By</span>
            <div class="text-xs font-semibold text-white">{{ $transfer->user->name }}</div>
            <div class="text-[11px] text-slate-400">{{ $transfer->user->department ?? 'Staff' }} &bull; {{ $transfer->purpose }}</div>
        </div>
    </div>

    <!-- DLP Detection Evaluation Results -->
    <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-white">DLP Detection Results</h3>
            <span class="text-xs text-slate-400 font-mono">{{ $transfer->detections->count() }} Trigger(s)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 border-b border-slate-800 font-medium">
                        <th class="pb-2">Technique</th>
                        <th class="pb-2">Triggered Rule</th>
                        <th class="pb-2">Evidence Sample</th>
                        <th class="pb-2 text-right">Severity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50">
                    @forelse($transfer->detections as $det)
                    <tr class="hover:bg-slate-800/20 transition">
                        <td class="py-2.5 pr-2 font-mono text-[11px] text-slate-400">
                            {{ str_replace('_', ' ', $det->detection_technique) }}
                        </td>
                        <td class="py-2.5 pr-2">
                            <div class="font-medium text-slate-200">{{ $det->rule_name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $det->details }}</div>
                        </td>
                        <td class="py-2.5 pr-2 font-mono text-[11px] text-slate-300 max-w-[200px] truncate" title="{{ $det->matched_sample }}">
                            {{ $det->matched_sample ?: 'N/A' }}
                        </td>
                        <td class="py-2.5 text-right">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                                {{ $det->severity === 'critical' ? 'bg-rose-500/20 text-rose-400' : ($det->severity === 'high' ? 'bg-orange-500/20 text-orange-400' : 'bg-amber-500/20 text-amber-400') }}">
                                {{ $det->severity }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-6 text-center text-slate-500">
                            Clean transfer. No sensitive data patterns or policy violations detected.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection