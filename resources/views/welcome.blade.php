<!DOCTYPE html>
<html lang="en" class="h-full scroll-smooth bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DataGuard - A Web-Based Data Loss Prevention and Monitoring System</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap');
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #020617;
            background-image: radial-gradient(circle at 50% 12%, rgba(37, 99, 235, 0.08) 0%, transparent 60%);
        }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="min-h-full flex flex-col antialiased">
    <!-- Top Navigation Bar -->
    <header class="h-16 bg-slate-900/80 backdrop-blur-md border-b border-slate-800/80 px-6 sm:px-10 flex items-center justify-between sticky top-0 z-40">
        <a href="{{ route('home') }}" class="flex items-center gap-2.5 group">
            <div class="w-8 h-8 rounded-lg bg-blue-600/15 border border-blue-500/30 text-blue-400 flex items-center justify-center transition group-hover:bg-blue-600 group-hover:text-white">
                <i class="fa-solid fa-shield-halved text-sm"></i>
            </div>
            <span class="text-base font-bold text-white tracking-tight">Data<span class="text-blue-400">Guard</span></span>
        </a>

        <nav class="hidden md:flex items-center gap-6 text-xs font-medium text-slate-300">
            <a href="#features" class="hover:text-blue-400 transition">Features</a>
            <a href="#how-it-works" class="hover:text-blue-400 transition">How It Works</a>
            <a href="{{ route('system-flow') }}" class="hover:text-blue-400 transition">System Flow</a>
        </nav>

        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs transition shadow-md shadow-blue-950">
                    <i class="fa-solid fa-gauge text-[11px]"></i>
                    <span>Go to Dashboard</span>
                </a>
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs transition shadow-md shadow-blue-950">
                    <i class="fa-solid fa-right-to-bracket text-[11px]"></i>
                    <span>Sign In</span>
                </a>
            @endauth
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1">
        <!-- Hero Section -->
        <section class="py-16 sm:py-24 px-6 max-w-5xl mx-auto text-center space-y-6">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-950/50 border border-blue-800/40 text-xs font-medium text-blue-400">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>Rule-Based Data Loss Prevention & Transfer Monitoring</span>
            </div>

            <!-- Main Heading & Title -->
            <div class="space-y-3">
                <h1 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight">
                    Data<span class="text-blue-400">Guard</span>
                </h1>
                <p class="text-sm sm:text-base font-semibold text-slate-300">
                    A Web-Based Data Loss Prevention and Monitoring System
                </p>
            </div>

            <!-- Short Description -->
            <p class="text-xs sm:text-sm text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Monitor, detect, and prevent unauthorized data transfers before sensitive information leaves the organization.
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
                @auth
                    <a href="{{ route('dashboard') }}" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs transition shadow-lg shadow-blue-950/60 flex items-center justify-center gap-2">
                        <span>Go to Dashboard</span>
                        <i class="fa-solid fa-arrow-right text-[11px]"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs transition shadow-lg shadow-blue-950/60 flex items-center justify-center gap-2">
                        <span>Get Started / Sign In</span>
                        <i class="fa-solid fa-arrow-right text-[11px]"></i>
                    </a>
                @endauth

                <a href="#features" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 border border-slate-800 text-slate-300 font-medium text-xs transition">
                    Learn More
                </a>
            </div>

            <!-- Live Sample Verdict Visual -->
            <div class="pt-10 max-w-3xl mx-auto">
                <div class="p-4 sm:p-5 rounded-2xl bg-slate-900/90 border border-slate-800 shadow-2xl space-y-3 text-left">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span class="font-mono text-slate-300 text-[11px]">DLP Policy Evaluation Matrix</span>
                        </div>
                        <span class="text-[10px] text-slate-500 font-mono">Real-Time Inspection</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                        <!-- Allowed State -->
                        <div class="p-3 rounded-xl bg-slate-950 border border-emerald-500/30 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/15 text-emerald-400">ALLOWED</span>
                                <span class="text-[10px] font-mono text-emerald-400">Risk &lt; 20</span>
                            </div>
                            <div class="text-xs font-medium text-slate-200">Compliant Data</div>
                            <p class="text-[11px] text-slate-400">Internal sharing to approved corporate endpoints cleared.</p>
                        </div>

                        <!-- Flagged State -->
                        <div class="p-3 rounded-xl bg-slate-950 border border-amber-500/30 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/15 text-amber-400">FLAGGED</span>
                                <span class="text-[10px] font-mono text-amber-400">Risk 20-59</span>
                            </div>
                            <div class="text-xs font-medium text-slate-200">Review Required</div>
                            <p class="text-[11px] text-slate-400">Moderate risk pattern or unusual burst queued for triage.</p>
                        </div>

                        <!-- Blocked State -->
                        <div class="p-3 rounded-xl bg-slate-950 border border-rose-500/30 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/15 text-rose-400">BLOCKED</span>
                                <span class="text-[10px] font-mono text-rose-400">Risk &ge; 60</span>
                            </div>
                            <div class="text-xs font-medium text-slate-200">Policy Violation</div>
                            <p class="text-[11px] text-slate-400">PII, credit cards, or scripts to external email stopped.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Features Section -->
        <section id="features" class="py-16 px-6 border-t border-slate-800/80 bg-slate-950/40">
            <div class="max-w-5xl mx-auto space-y-10">
                <div class="text-center space-y-2">
                    <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Core System Features</h2>
                    <p class="text-xs sm:text-sm text-slate-400">Essential capabilities powering DataGuard's transfer inspection engine</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Feature 1 -->
                    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-2.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-600/15 border border-blue-500/30 text-blue-400 flex items-center justify-center">
                            <i class="fa-solid fa-file-shield text-base"></i>
                        </div>
                        <h3 class="text-sm font-semibold text-white">Detect Sensitive Data</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Identify sensitive information such as PII, financial information, and credentials using rule-based detection.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-2.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-600/15 border border-blue-500/30 text-blue-400 flex items-center justify-center">
                            <i class="fa-solid fa-scale-balanced text-base"></i>
                        </div>
                        <h3 class="text-sm font-semibold text-white">Check Security Policies</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Evaluate transfer requests against configured security policies and approved destinations.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-2.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-600/15 border border-blue-500/30 text-blue-400 flex items-center justify-center">
                            <i class="fa-solid fa-ban text-base"></i>
                        </div>
                        <h3 class="text-sm font-semibold text-white">Prevent Data Loss</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Allow, flag, or block transfers based on detected risks and policy violations.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 space-y-2.5">
                        <div class="w-10 h-10 rounded-xl bg-blue-600/15 border border-blue-500/30 text-blue-400 flex items-center justify-center">
                            <i class="fa-solid fa-chart-line text-base"></i>
                        </div>
                        <h3 class="text-sm font-semibold text-white">Monitor Activity</h3>
                        <p class="text-xs text-slate-400 leading-relaxed">
                            Record transfer activity, alerts, and security events for review.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section id="how-it-works" class="py-16 px-6 border-t border-slate-800/80">
            <div class="max-w-4xl mx-auto space-y-10">
                <div class="text-center space-y-2">
                    <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">How It Works</h2>
                    <p class="text-xs sm:text-sm text-slate-400">The six-step core DLP lifecycle for outbound transfer inspection</p>
                </div>

                <!-- Process Sequence Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <!-- Step 1 -->
                    <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 flex items-start gap-3">
                        <span class="w-7 h-7 rounded-lg bg-blue-600/15 text-blue-400 flex items-center justify-center font-bold text-xs shrink-0">1</span>
                        <div>
                            <div class="text-xs font-semibold text-white">Submit Transfer</div>
                            <p class="text-[11px] text-slate-400 mt-0.5">User submits file or text payload with destination and purpose.</p>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 flex items-start gap-3">
                        <span class="w-7 h-7 rounded-lg bg-blue-600/15 text-blue-400 flex items-center justify-center font-bold text-xs shrink-0">2</span>
                        <div>
                            <div class="text-xs font-semibold text-white">Scan Data</div>
                            <p class="text-[11px] text-slate-400 mt-0.5">Engine inspects content for PII, credit cards, and credentials.</p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 flex items-start gap-3">
                        <span class="w-7 h-7 rounded-lg bg-blue-600/15 text-blue-400 flex items-center justify-center font-bold text-xs shrink-0">3</span>
                        <div>
                            <div class="text-xs font-semibold text-white">Check Policy</div>
                            <p class="text-[11px] text-slate-400 mt-0.5">Cross-references destination whitelist and active security rules.</p>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="p-4 rounded-xl bg-slate-900 border border-blue-500/40 flex items-start gap-3">
                        <span class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0">4</span>
                        <div>
                            <div class="text-xs font-semibold text-white">Allow / Flag / Block</div>
                            <p class="text-[11px] text-slate-400 mt-0.5">Calculates risk score (0–100) and executes automated verdict.</p>
                        </div>
                    </div>

                    <!-- Step 5 -->
                    <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 flex items-start gap-3">
                        <span class="w-7 h-7 rounded-lg bg-blue-600/15 text-blue-400 flex items-center justify-center font-bold text-xs shrink-0">5</span>
                        <div>
                            <div class="text-xs font-semibold text-white">Log Activity</div>
                            <p class="text-[11px] text-slate-400 mt-0.5">Immutable transfer audit trail recorded in the database.</p>
                        </div>
                    </div>

                    <!-- Step 6 -->
                    <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 flex items-start gap-3">
                        <span class="w-7 h-7 rounded-lg bg-rose-500/15 text-rose-400 flex items-center justify-center font-bold text-xs shrink-0">6</span>
                        <div>
                            <div class="text-xs font-semibold text-white">Alert if Required</div>
                            <p class="text-[11px] text-slate-400 mt-0.5">Incidents queued for security analyst investigation.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action Section -->
        <section class="py-16 px-6 max-w-4xl mx-auto">
            <div class="p-8 sm:p-10 rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl text-center space-y-4">
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">
                    Protect sensitive data before it leaves your organization.
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 max-w-xl mx-auto">
                    Sign in to the DataGuard console to inspect outbound transfers, manage detection policies, and monitor security events.
                </p>
                <div class="pt-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs transition shadow-lg shadow-blue-950">
                            <span>Go to Dashboard</span>
                            <i class="fa-solid fa-arrow-right text-[11px]"></i>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-semibold text-xs transition shadow-lg shadow-blue-950">
                            <span>Sign In</span>
                            <i class="fa-solid fa-arrow-right text-[11px]"></i>
                        </a>
                    @endauth
                </div>
            </div>
        </section>
    </main>

    <!-- Minimal Footer -->
    <footer class="h-14 border-t border-slate-800/80 px-6 sm:px-10 flex items-center justify-between text-xs text-slate-400 bg-slate-950">
        <div>
            Data<span class="text-blue-400 font-semibold">Guard</span> &bull; A Web-Based Data Loss Prevention System
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('system-flow') }}" class="hover:text-white transition">Architecture Flow</a>
            <span>&bull;</span>
            <a href="{{ route('login') }}" class="hover:text-white transition">Console Access</a>
        </div>
    </footer>
</body>
</html>
