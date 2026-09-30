<header class="h-14 bg-slate-900 border-b border-slate-800 px-6 flex items-center justify-between sticky top-0 z-30">
    <div class="text-sm text-slate-400">
        <span class="text-slate-200 font-medium capitalize">{{ str_replace(['.', '-', '_'], ' ', Route::currentRouteName() ?? 'Dashboard') }}</span>
    </div>

    <div class="flex items-center gap-3">
        <a href="{{ route('transfers.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs transition">
            <i class="fa-solid fa-plus text-[10px]"></i>
            <span>New Transfer</span>
        </a>

        <div class="h-5 w-px bg-slate-800"></div>

        <div class="text-right hidden sm:block">
            <div class="text-xs text-slate-300">{{ auth()->user()->name }}</div>
            <div class="text-[10px] text-slate-500">{{ auth()->user()->email }}</div>
        </div>

        <form action="{{ route('logout') }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="p-1.5 rounded-lg hover:bg-slate-800 text-slate-400 hover:text-rose-400 transition" title="Log Out">
                <i class="fa-solid fa-right-from-bracket text-sm"></i>
            </button>
        </form>
    </div>
</header>