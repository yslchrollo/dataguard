<aside class="w-60 bg-slate-900 border-r border-slate-800 flex flex-col shrink-0 h-screen">
    <!-- Brand -->
    <a href="{{ route('dashboard') }}" class="h-14 px-5 flex items-center border-b border-slate-800 hover:opacity-90 transition">
        <x-logo variant="icon" size="md" />
    </a>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto text-[13px]">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-blue-600/15 text-blue-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <i class="fa-solid fa-gauge w-4 text-center text-xs"></i>
            <span>Dashboard</span>
        </a>

        <div class="pt-4 pb-1.5 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Monitoring</div>

        <a href="{{ route('transfers.create') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('transfers.create') ? 'bg-blue-600/15 text-blue-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <i class="fa-solid fa-cloud-arrow-up w-4 text-center text-xs"></i>
            <span>New Transfer</span>
        </a>

        <a href="{{ route('transfers.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('transfers.index') || request()->routeIs('transfers.show') ? 'bg-blue-600/15 text-blue-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <i class="fa-solid fa-list w-4 text-center text-xs"></i>
            <span>Transfer Logs</span>
        </a>

        <a href="{{ route('alerts.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('alerts.*') ? 'bg-blue-600/15 text-blue-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <i class="fa-solid fa-bell w-4 text-center text-xs"></i>
            <span>Security Alerts</span>
        </a>

        @if(auth()->user()->isAdmin() || auth()->user()->isAnalyst())
        <div class="pt-4 pb-1.5 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Security</div>

        <a href="{{ route('reports.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('reports.*') ? 'bg-blue-600/15 text-blue-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <i class="fa-solid fa-chart-bar w-4 text-center text-xs"></i>
            <span>Reports</span>
        </a>
        @endif

        @if(auth()->user()->isAdmin())
        <div class="pt-4 pb-1.5 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Administration</div>

        <a href="{{ route('users.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('users.*') ? 'bg-blue-600/15 text-blue-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <i class="fa-solid fa-users w-4 text-center text-xs"></i>
            <span>Users</span>
        </a>

        <a href="{{ route('policies.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('policies.*') ? 'bg-blue-600/15 text-blue-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <i class="fa-solid fa-scale-balanced w-4 text-center text-xs"></i>
            <span>Security Policies</span>
        </a>

        <a href="{{ route('categories.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('categories.*') ? 'bg-blue-600/15 text-blue-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <i class="fa-solid fa-tags w-4 text-center text-xs"></i>
            <span>Data Categories</span>
        </a>
        @endif

        <div class="pt-4 pb-1.5 px-3 text-[10px] font-semibold uppercase tracking-widest text-slate-500">Reference</div>

        <a href="{{ route('system-flow') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition {{ request()->routeIs('system-flow') ? 'bg-blue-600/15 text-blue-400 font-semibold' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
            <i class="fa-solid fa-diagram-project w-4 text-center text-xs"></i>
            <span>System Flow</span>
        </a>
    </nav>

    <!-- User + Demo Switcher -->
    <div class="p-3 border-t border-slate-800 space-y-2">
        <div class="px-2 flex items-center gap-2.5">
            <div class="w-7 h-7 rounded-full bg-slate-700 flex items-center justify-center text-xs text-slate-300 font-bold">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-xs font-medium text-slate-200 truncate">{{ auth()->user()->name }}</div>
                <div class="text-[10px] text-slate-500 capitalize">{{ auth()->user()->role === 'admin' ? 'Administrator' : (auth()->user()->role === 'analyst' ? 'Security Analyst' : 'Employee') }}</div>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-1">
            <a href="{{ route('login.quick', 'admin') }}" class="text-center py-1 rounded text-[10px] font-medium transition {{ auth()->user()->isAdmin() ? 'bg-blue-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700' }}">Admin</a>
            <a href="{{ route('login.quick', 'analyst') }}" class="text-center py-1 rounded text-[10px] font-medium transition {{ auth()->user()->isAnalyst() ? 'bg-blue-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700' }}">Analyst</a>
            <a href="{{ route('login.quick', 'user') }}" class="text-center py-1 rounded text-[10px] font-medium transition {{ auth()->user()->isUser() ? 'bg-blue-600 text-white' : 'bg-slate-800 text-slate-400 hover:bg-slate-700' }}">User</a>
        </div>
    </div>
</aside>