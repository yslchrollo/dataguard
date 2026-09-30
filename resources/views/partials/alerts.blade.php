@if (session('success'))
<div class="p-3 text-xs text-emerald-300 rounded-lg bg-emerald-950/40 border border-emerald-800/40 flex items-center justify-between" role="alert">
    <div class="flex items-center gap-2">
        <i class="fa-solid fa-circle-check text-emerald-400"></i>
        <span>{{ session('success') }}</span>
    </div>
    <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>
@endif

@if (session('error'))
<div class="p-3 text-xs text-rose-300 rounded-lg bg-rose-950/40 border border-rose-800/40 flex items-center justify-between" role="alert">
    <div class="flex items-center gap-2">
        <i class="fa-solid fa-triangle-exclamation text-rose-400"></i>
        <span>{{ session('error') }}</span>
    </div>
    <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-200">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>
@endif

@if (session('warning'))
<div class="p-3 text-xs text-amber-300 rounded-lg bg-amber-950/40 border border-amber-800/40 flex items-center justify-between" role="alert">
    <div class="flex items-center gap-2">
        <i class="fa-solid fa-circle-exclamation text-amber-400"></i>
        <span>{{ session('warning') }}</span>
    </div>
    <button type="button" onclick="this.parentElement.remove()" class="text-amber-400 hover:text-amber-200">
        <i class="fa-solid fa-xmark"></i>
    </button>
</div>
@endif

@if ($errors->any())
<div class="p-3 text-xs text-rose-300 rounded-lg bg-rose-950/40 border border-rose-800/40">
    <div class="font-medium text-rose-200 mb-1 flex items-center gap-1.5">
        <i class="fa-solid fa-triangle-exclamation text-rose-400"></i>
        <span>Please check the following input errors:</span>
    </div>
    <ul class="list-disc list-inside space-y-0.5 pl-2 text-rose-300">
        @foreach ($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif