<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - DataGuard DLP</title>
    <link rel="icon" type="image/png" href="{{ asset('images/dataguard-icon-transparent.png') }}">
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
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap');
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0b1120; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #334155; }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-slate-950 flex overflow-hidden">
    <!-- Sidebar -->
    @include('partials.sidebar')

    <!-- Main Content Shell -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden">
        <!-- Top Navbar -->
        @include('partials.navbar')

        <!-- Page Main Scrollable Container -->
        <main class="flex-1 overflow-y-auto p-5 sm:p-6 lg:p-8 bg-slate-950/80">
            <div class="max-w-6xl mx-auto space-y-6">
                <!-- Flash Alerts -->
                @include('partials.alerts')

                <!-- Primary Content -->
                @yield('content')
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>