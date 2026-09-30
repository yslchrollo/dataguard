@extends('layouts.app')

@section('title', 'Investigate Alert: ' . $alert->alert_uuid)

@section('content')
<div class="space-y-5 max-w-4xl mx-auto">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <a href="{{ route('alerts.index') }}" class="text-xs text-slate-400 hover:text-white transition flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left text-[10px]"></i>
            <span>Back to Alerts</span>
        </a>
        <span class="text-xs font-mono text-slate-400">Incident: {{ $alert->alert_uuid }}</span>
    </div>

    <!-- Alert Title Card -->
    <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                    {{ $alert->severity === 'critical' ? 'bg-rose-500/20 text-rose-400' : ($alert->severity === 'high' ? 'bg-orange-500/20 text-orange-400' : 'bg-amber-500/20 text-amber-400') }}">
                    {{ $alert->severity }} Severity
                </span>
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase
                    {{ $alert->status === 'resolved' ? 'bg-emerald-500/20 text-emerald-400' : ($alert->status === 'under_review' ? 'bg-blue-500/20 text-blue-400' : 'bg-rose-500/20 text-rose-400') }}">
                    {{ str_replace('_', ' ', $alert->status) }}
                </span>
            </div>
            <h1 class="text-base font-bold text-white mt-1">{{ $alert->title }}</h1>
            <p class="text-xs text-slate-400 mt-0.5">{{ $alert->description }}</p>
        </div>
        <div class="text-xs font-mono text-slate-400 shrink-0">
            {{ $alert->created_at->format('M d, Y H:i:s') }}
        </div>
    </div>

    <!-- Two Column: Transfer Details & Investigation Form -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <!-- Left: Incident Context -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-3">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-300">
                Transfer Details
            </h3>

            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                    <span class="text-slate-400">File Name:</span>
                    <span class="font-medium text-white">{{ $alert->transfer->file_name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                    <span class="text-slate-400">Destination:</span>
                    <span class="font-mono text-slate-200">{{ $alert->transfer->destination ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                    <span class="text-slate-400">Submitted By:</span>
                    <span class="text-slate-200">{{ $alert->user->name }} ({{ $alert->user->department ?? 'Staff' }})</span>
                </div>
                <div class="flex justify-between py-1.5 border-b border-slate-800/60">
                    <span class="text-slate-400">DLP Verdict:</span>
                    <span class="font-bold uppercase {{ ($alert->transfer->decision ?? '') === 'blocked' ? 'text-rose-400' : 'text-amber-400' }}">
                        {{ strtoupper($alert->transfer->decision ?? 'FLAGGED') }} (Risk Score: {{ $alert->transfer->risk_score ?? 0 }})
                    </span>
                </div>
            </div>

            <!-- Triggered Rules -->
            <div class="pt-2">
                <span class="text-[11px] font-medium text-slate-400 uppercase block mb-1.5">Triggered Detection Rules:</span>
                <div class="space-y-1.5">
                    @forelse($alert->transfer->detections ?? [] as $det)
                    <div class="p-2.5 rounded-lg bg-slate-950 border border-slate-800 text-xs">
                        <div class="flex items-center justify-between font-medium text-slate-200">
                            <span>{{ $det->rule_name }}</span>
                            <span class="text-[10px] uppercase font-bold text-rose-400">{{ $det->severity }}</span>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-0.5">{{ $det->details }}</div>
                        @if($det->matched_sample)
                        <div class="text-[10px] font-mono text-slate-300 mt-1 bg-slate-900/80 p-1 rounded">
                            Sample: {{ $det->matched_sample }}
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="text-slate-500 text-xs">No explicit sub-rules recorded.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right: Investigation Form -->
        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800 space-y-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-300">
                Analyst Investigation & Resolution
            </h3>

            @if(!auth()->user()->isUser())
            <form method="POST" action="{{ route('alerts.update', $alert->id) }}" class="space-y-3.5">
                @csrf
                @method('PUT')

                <div>
                    <label for="status" class="block text-xs font-medium text-slate-400 mb-1">Incident Status</label>
                    <select name="status" id="status" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="open" {{ $alert->status === 'open' ? 'selected' : '' }}>Open (Awaiting Triage)</option>
                        <option value="under_review" {{ $alert->status === 'under_review' ? 'selected' : '' }}>Under Review (Investigation in Progress)</option>
                        <option value="resolved" {{ $alert->status === 'resolved' ? 'selected' : '' }}>Resolved (Remediation Complete)</option>
                        <option value="dismissed" {{ $alert->status === 'dismissed' ? 'selected' : '' }}>Dismissed (Authorized Exception)</option>
                    </select>
                </div>

                <div>
                    <label for="action_taken" class="block text-xs font-medium text-slate-400 mb-1">Response Action <span class="text-rose-400">*</span></label>
                    <select name="action_taken" id="action_taken" required class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">-- Select Action Taken --</option>
                        <option value="Confirmed Policy Violation - Block Maintained" {{ $alert->action_taken === 'Confirmed Policy Violation - Block Maintained' ? 'selected' : '' }}>Confirmed Policy Violation - Block Maintained</option>
                        <option value="Authorized Corporate Exception Granted" {{ $alert->action_taken === 'Authorized Corporate Exception Granted' ? 'selected' : '' }}>Authorized Corporate Exception Granted</option>
                        <option value="User Contacted & Security Retraining Mandated" {{ $alert->action_taken === 'User Contacted & Security Retraining Mandated' ? 'selected' : '' }}>User Contacted & Security Retraining Mandated</option>
                        <option value="Escalated to IT Security Lead & Legal" {{ $alert->action_taken === 'Escalated to IT Security Lead & Legal' ? 'selected' : '' }}>Escalated to IT Security Lead & Legal</option>
                    </select>
                </div>

                <div>
                    <label for="investigation_notes" class="block text-xs font-medium text-slate-400 mb-1">Investigation Notes <span class="text-rose-400">*</span></label>
                    <textarea name="investigation_notes" id="investigation_notes" rows="3" required placeholder="Enter analysis findings, employee contact, and remediation details..."
                        class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500">{{ old('investigation_notes', $alert->investigation_notes) }}</textarea>
                </div>

                <button type="submit" class="w-full py-2.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs transition">
                    Save Investigation & Update Status
                </button>
            </form>
            @else
            <!-- Read-Only View for regular users -->
            <div class="space-y-3 text-xs">
                <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                    <span class="text-slate-400 block text-[11px] mb-1">Action Assigned:</span>
                    <span class="text-white font-medium">{{ $alert->action_taken ?: 'Awaiting Review' }}</span>
                </div>
                <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                    <span class="text-slate-400 block text-[11px] mb-1">Investigation Notes:</span>
                    <p class="text-slate-300">{{ $alert->investigation_notes ?: 'No notes recorded.' }}</p>
                </div>
            </div>
            @endif

            @if($alert->investigator)
            <div class="pt-2 border-t border-slate-800 text-[11px] text-slate-400 flex items-center justify-between">
                <span>Investigator: <strong class="text-white">{{ $alert->investigator->name }}</strong></span>
                <span class="font-mono">{{ $alert->resolved_at?->diffForHumans() ?? 'In Progress' }}</span>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection