@extends('layouts.app')

@section('title', 'Submit Transfer Request')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="pb-2 border-b border-slate-800/80">
        <h1 class="text-xl font-bold text-white tracking-tight">Submit Transfer Request</h1>
        <p class="text-xs text-slate-400 mt-0.5">Outbound transfer data is scanned for sensitive content and policy compliance before egress.</p>
    </div>

    <!-- Quick Demo Scenarios -->
    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 space-y-2">
        <div class="flex items-center justify-between text-xs">
            <span class="font-semibold text-slate-300 flex items-center gap-1.5">
                <i class="fa-solid fa-wand-magic-sparkles text-blue-400"></i>
                <span>Demo Scenario Presets</span>
            </span>
            <span class="text-[11px] text-slate-500">Click to autofill test data</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
            <button type="button" onclick="loadScenario('blocked_pii')" class="p-2 rounded-lg bg-rose-950/30 hover:bg-rose-900/40 border border-rose-800/40 text-left transition">
                <div class="text-xs font-semibold text-rose-300">1. Blocked: PII & Cards</div>
                <div class="text-[10px] text-slate-400">Payroll + Credit Cards to Gmail</div>
            </button>
            <button type="button" onclick="loadScenario('blocked_script')" class="p-2 rounded-lg bg-amber-950/30 hover:bg-amber-900/40 border border-amber-800/40 text-left transition">
                <div class="text-xs font-semibold text-amber-300">2. Blocked: Script & Passwords</div>
                <div class="text-[10px] text-slate-400">Batch Script + Plaintext Credentials</div>
            </button>
            <button type="button" onclick="loadScenario('allowed_internal')" class="p-2 rounded-lg bg-emerald-950/30 hover:bg-emerald-900/40 border border-emerald-800/40 text-left transition">
                <div class="text-xs font-semibold text-emerald-300">3. Allowed: Compliant</div>
                <div class="text-[10px] text-slate-400">Clean Report to Corporate Domain</div>
            </button>
        </div>
    </div>

    <!-- Main Transfer Submission Form -->
    <div class="p-6 rounded-xl bg-slate-900 border border-slate-800">
        <form id="transferForm" method="POST" action="{{ route('transfers.store') }}" enctype="multipart/form-data" class="space-y-5">
            @csrf

            <!-- Step 1: What are you transferring? -->
            <div class="space-y-3">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                    1. What are you transferring?
                </label>
                
                <div class="relative border border-dashed border-slate-700 hover:border-blue-500 rounded-xl p-5 text-center transition bg-slate-950/40">
                    <input type="file" name="file" id="fileInput" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="handleFileSelected(this)">
                    <div class="space-y-1 pointer-events-none">
                        <i class="fa-solid fa-cloud-arrow-up text-xl text-blue-400 mb-1" id="uploadIcon"></i>
                        <div class="text-xs font-medium text-slate-200" id="fileLabel">Choose a file or drag here</div>
                        <p class="text-[11px] text-slate-500">PDF, DOCX, CSV, TXT, JSON, Code, etc. (Max 50MB)</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    <div>
                        <label for="file_name_override" class="block text-[11px] font-medium text-slate-400 mb-1">File / Artifact Name</label>
                        <input type="text" name="file_name_override" id="file_name_override" value="{{ old('file_name_override') }}" placeholder="e.g. quarterly_payroll.csv"
                            class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="content_payload" class="block text-[11px] font-medium text-slate-400 mb-1">Direct Text Sample (Optional)</label>
                        <input type="text" name="content_payload" id="content_payload" placeholder="Paste data snippet if not uploading file..."
                            class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Step 2: Where are you sending it? -->
            <div class="space-y-1.5 pt-2 border-t border-slate-800">
                <label for="destination" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                    2. Where are you sending it? <span class="text-rose-400">*</span>
                </label>
                <input type="text" name="destination" id="destination" value="{{ old('destination') }}" required placeholder="e.g. partner@external.org or team@company.com"
                    class="w-full px-3.5 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500 font-mono">
            </div>

            <!-- Step 3: Why are you sending it? -->
            <div class="space-y-3 pt-2 border-t border-slate-800">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                    3. Why are you sending it? <span class="text-rose-400">*</span>
                </label>
                
                <select name="purpose" id="purpose" required class="w-full px-3.5 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">-- Select Purpose / Reason --</option>
                    <option value="Personal Backup / Working From Home">Personal Backup / Working From Home</option>
                    <option value="Quarterly Financial Audit Submission">Quarterly Financial Audit Submission</option>
                    <option value="Vendor Integration & System Sync">Vendor Integration & System Sync</option>
                    <option value="Internal Inter-Departmental Sharing">Internal Inter-Departmental Sharing</option>
                    <option value="Client Project Deliverable Delivery">Client Project Deliverable Delivery</option>
                    <option value="Software Source Code Maintenance">Software Source Code Maintenance</option>
                    <option value="Other Business Purpose">Other Business Purpose</option>
                </select>

                <div>
                    <input type="text" name="description" id="description" value="{{ old('description') }}" placeholder="Additional notes or context (optional)..."
                        class="w-full px-3.5 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                <a href="{{ route('transfers.index') }}" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-medium transition">
                    Cancel
                </a>
                
                <button type="submit" id="submitBtn" class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-shield-halved text-xs"></i>
                    <span>Scan & Submit Transfer</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Scanning Animation Modal -->
<div id="scanningModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="max-w-sm w-full p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl space-y-4 text-center">
        <div class="w-12 h-12 rounded-full bg-blue-600/20 text-blue-400 flex items-center justify-center mx-auto text-xl">
            <i class="fa-solid fa-circle-notch animate-spin"></i>
        </div>

        <div>
            <h3 class="text-sm font-bold text-white">Inspecting Transfer Data...</h3>
            <p class="text-xs text-slate-400 mt-1">Executing 5-Technique Rule-Based DLP Inspection</p>
        </div>

        <div class="space-y-2 text-left text-xs font-mono bg-slate-950 p-3 rounded-lg border border-slate-800/80">
            <div class="flex items-center gap-2 text-blue-400" id="step1">
                <i class="fa-solid fa-check text-[10px]"></i>
                <span>1. Sensitive Data Inspection</span>
            </div>
            <div class="flex items-center gap-2 text-slate-400" id="step2">
                <i class="fa-solid fa-circle-notch animate-spin text-[10px]"></i>
                <span>2. Destination Verification</span>
            </div>
            <div class="flex items-center gap-2 text-slate-400" id="step3">
                <i class="fa-regular fa-circle text-[10px]"></i>
                <span>3. Policy Evaluation</span>
            </div>
            <div class="flex items-center gap-2 text-slate-400" id="step4">
                <i class="fa-regular fa-circle text-[10px]"></i>
                <span>4. Calculating Decision</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function handleFileSelected(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('fileLabel').innerText = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
            document.getElementById('file_name_override').value = file.name;
            document.getElementById('uploadIcon').className = 'fa-solid fa-file-circle-check text-xl text-emerald-400 mb-1';
        }
    }

    function loadScenario(type) {
        if (type === 'blocked_pii') {
            document.getElementById('file_name_override').value = 'q3_employee_payroll_records.csv';
            document.getElementById('destination').value = 'personal.backup@gmail.com';
            document.getElementById('purpose').value = 'Personal Backup / Working From Home';
            document.getElementById('description').value = 'Exported quarterly salary balance sheet.';
            document.getElementById('content_payload').value = `EmployeeID,FullName,NationalID,CreditCard,MonthlySalary\n101,Juan Dela Cruz,123-456-7890,4532-1188-9922-3344,85000\n102,Maria Santos,987-654-3210,5424-0011-2233-4455,92000`;
            document.getElementById('fileLabel').innerText = 'Simulated: q3_employee_payroll_records.csv';
        } else if (type === 'blocked_script') {
            document.getElementById('file_name_override').value = 'database_dump_tool.bat';
            document.getElementById('destination').value = 'contractor@yahoo.com';
            document.getElementById('purpose').value = 'Software Source Code Maintenance';
            document.getElementById('description').value = 'Automated backup batch script with credentials.';
            document.getElementById('content_payload').value = `@echo off\nset db_pass=SuperAdminSecretPassword2026!\nmysqldump -u root -p%db_pass% vault > dump.sql`;
            document.getElementById('fileLabel').innerText = 'Simulated: database_dump_tool.bat';
        } else if (type === 'allowed_internal') {
            document.getElementById('file_name_override').value = 'q3_marketing_report.pdf';
            document.getElementById('destination').value = 'marketing.team@company.com';
            document.getElementById('purpose').value = 'Internal Inter-Departmental Sharing';
            document.getElementById('description').value = 'Compliant branding guidelines document.';
            document.getElementById('content_payload').value = `DataGuard Marketing Strategy 2026. Clean document for internal team review.`;
            document.getElementById('fileLabel').innerText = 'Simulated: q3_marketing_report.pdf';
        }
    }

    document.getElementById('transferForm').addEventListener('submit', function(e) {
        document.getElementById('scanningModal').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('step2').className = 'flex items-center gap-2 text-blue-400';
            document.getElementById('step2').innerHTML = '<i class="fa-solid fa-check text-[10px]"></i><span>2. Destination Verification</span>';
            document.getElementById('step3').className = 'flex items-center gap-2 text-blue-400';
            document.getElementById('step3').innerHTML = '<i class="fa-solid fa-circle-notch animate-spin text-[10px]"></i><span>3. Policy Evaluation</span>';
        }, 500);
        setTimeout(() => {
            document.getElementById('step3').className = 'flex items-center gap-2 text-blue-400';
            document.getElementById('step3').innerHTML = '<i class="fa-solid fa-check text-[10px]"></i><span>3. Policy Evaluation</span>';
            document.getElementById('step4').className = 'flex items-center gap-2 text-blue-400';
            document.getElementById('step4').innerHTML = '<i class="fa-solid fa-circle-notch animate-spin text-[10px]"></i><span>4. Calculating Decision</span>';
        }, 1000);
    });
</script>
@endpush