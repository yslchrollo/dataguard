@extends('layouts.app')

@section('title', 'System Flow & Architecture')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">System Flow</h1>
            <p class="text-xs text-slate-400 mt-0.5">Core 6-step lifecycle of DataGuard DLP transfer inspection and decision workflow</p>
        </div>
        <a href="{{ route('transfers.create') }}" class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs transition inline-flex items-center gap-1.5 self-start sm:self-auto">
            <i class="fa-solid fa-play text-[10px]"></i>
            <span>Test Transfer Flow</span>
        </a>
    </div>

    <!-- 6-Step Visual Flow Cards -->
    <div class="space-y-3">
        <!-- Step 1 -->
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 flex items-start gap-4">
            <div class="w-8 h-8 rounded-lg bg-blue-600/15 text-blue-400 flex items-center justify-center font-bold text-xs shrink-0">
                1
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white">Transfer Submission</h3>
                <p class="text-xs text-slate-400 mt-0.5">User uploads a file or inputs text, specifies the target destination endpoint, and provides a business reason.</p>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 flex items-start gap-4">
            <div class="w-8 h-8 rounded-lg bg-blue-600/15 text-blue-400 flex items-center justify-center font-bold text-xs shrink-0">
                2
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white">Scan Data (Regex & Keywords)</h3>
                <p class="text-xs text-slate-400 mt-0.5">Rule-based content inspection executes pattern matching for PII (IDs, TIN, SSS), Financial data (Credit cards with Luhn check), and Credentials (passwords, private keys).</p>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 flex items-start gap-4">
            <div class="w-8 h-8 rounded-lg bg-blue-600/15 text-blue-400 flex items-center justify-center font-bold text-xs shrink-0">
                3
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white">Check Security Policies</h3>
                <p class="text-xs text-slate-400 mt-0.5">Evaluates destination domain against approved internal whitelists vs unapproved webmails, executable file extensions, and transfer rate limits.</p>
            </div>
        </div>

        <!-- Step 4: Decision -->
        <div class="p-4 rounded-xl bg-slate-900 border border-blue-500/40 space-y-3">
            <div class="flex items-start gap-4">
                <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                    4
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">DLP Automated Decision</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Calculates risk score (0–100) and executes verdict:</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1">
                <div class="p-3 rounded-lg bg-emerald-950/30 border border-emerald-500/30 text-center">
                    <span class="text-xs font-bold text-emerald-400 block">ALLOW</span>
                    <span class="text-[11px] text-slate-400">Compliant transfer cleared for outbound delivery.</span>
                </div>
                <div class="p-3 rounded-lg bg-amber-950/30 border border-amber-500/30 text-center">
                    <span class="text-xs font-bold text-amber-400 block">FLAG</span>
                    <span class="text-[11px] text-slate-400">Moderate risk pattern requires review.</span>
                </div>
                <div class="p-3 rounded-lg bg-rose-950/30 border border-rose-500/30 text-center">
                    <span class="text-xs font-bold text-rose-400 block">BLOCK</span>
                    <span class="text-[11px] text-slate-400">Policy violation halted immediately.</span>
                </div>
            </div>
        </div>

        <!-- Step 5 -->
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 flex items-start gap-4">
            <div class="w-8 h-8 rounded-lg bg-blue-600/15 text-blue-400 flex items-center justify-center font-bold text-xs shrink-0">
                5
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white">Log Activity & Audit Trail</h3>
                <p class="text-xs text-slate-400 mt-0.5">Immutable record of transfer metadata, destination, user identity, and detection findings are logged for auditing.</p>
            </div>
        </div>

        <!-- Step 6 -->
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 flex items-start gap-4">
            <div class="w-8 h-8 rounded-lg bg-rose-600/15 text-rose-400 flex items-center justify-center font-bold text-xs shrink-0">
                6
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white">Generate Alert & Analyst Review</h3>
                <p class="text-xs text-slate-400 mt-0.5">If Blocked or Flagged, a security incident alert is created for the Security Analyst to investigate, record notes, and assign remediation.</p>
            </div>
        </div>
    </div>
</div>
@endsection