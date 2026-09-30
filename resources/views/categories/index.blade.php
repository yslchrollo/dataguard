@extends('layouts.app')

@section('title', 'Sensitive Data Categories')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-800/80">
        <div>
            <h1 class="text-xl font-bold text-white tracking-tight">Sensitive Data Categories</h1>
            <p class="text-xs text-slate-400 mt-0.5">Classification rules, regular expressions, and keyword matrices</p>
        </div>
        <button type="button" onclick="document.getElementById('addCategoryModal').classList.remove('hidden')" class="px-3.5 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs transition inline-flex items-center gap-1.5 self-start sm:self-auto">
            <i class="fa-solid fa-plus text-[10px]"></i>
            <span>Add Category</span>
        </button>
    </div>

    <!-- Category Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($categories as $cat)
        <div class="p-4 rounded-xl bg-slate-900 border border-slate-800 space-y-3 flex flex-col justify-between">
            <div class="space-y-2">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-semibold text-white">{{ $cat->name }}</h3>
                        <span class="text-xs font-mono text-blue-400">{{ $cat->code }}</span>
                    </div>
                    <form action="{{ route('categories.toggle', $cat->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase transition
                            {{ $cat->is_active ? 'bg-emerald-500/15 text-emerald-400' : 'bg-slate-800 text-slate-500' }}">
                            {{ $cat->is_active ? 'Enabled' : 'Disabled' }}
                        </button>
                    </form>
                </div>

                <p class="text-xs text-slate-400">{{ $cat->description }}</p>

                <!-- Keywords List -->
                <div class="pt-1">
                    <div class="text-[10px] uppercase font-semibold text-slate-500 mb-1">Keywords / Patterns:</div>
                    <div class="flex flex-wrap gap-1 max-h-20 overflow-y-auto">
                        @if(is_array($cat->patterns))
                            @foreach($cat->patterns as $pattern)
                            <span class="px-1.5 py-0.5 rounded bg-slate-950 border border-slate-800 text-[10px] font-mono text-slate-300">
                                {{ $pattern }}
                            </span>
                            @endforeach
                        @else
                            <span class="text-xs text-slate-500">None</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="pt-2 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                <span>Detections: <strong class="text-white">{{ $cat->detections_count ?? 0 }}</strong></span>
                <span class="font-mono uppercase text-[10px] font-bold
                    {{ $cat->risk_level === 'critical' ? 'text-rose-400' : ($cat->risk_level === 'high' ? 'text-orange-400' : 'text-blue-400') }}">
                    {{ $cat->risk_level }} Risk
                </span>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Modal for adding category -->
<div id="addCategoryModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 hidden">
    <div class="max-w-md w-full p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-bold text-white">Add Data Category</h3>
            <button type="button" onclick="document.getElementById('addCategoryModal').classList.add('hidden')" class="text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('categories.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Category Name *</label>
                <input type="text" name="name" required placeholder="e.g. Health Records (HIPAA)"
                    class="w-full px-3 py-1.5 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Category Code *</label>
                <input type="text" name="code" required placeholder="e.g. HEALTH_DATA"
                    class="w-full px-3 py-1.5 bg-slate-950 border border-slate-700 rounded-lg text-xs font-mono text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Description</label>
                <textarea name="description" rows="2" placeholder="Describe sensitivity..."
                    class="w-full px-3 py-1.5 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Risk Severity *</label>
                <select name="risk_level" required class="w-full px-3 py-1.5 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="critical">Critical</option>
                    <option value="high" selected>High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-400 mb-1">Comma-Separated Keywords</label>
                <textarea name="keywords_input" rows="2" placeholder="diagnosis, patient id, prescription"
                    class="w-full px-3 py-1.5 bg-slate-950 border border-slate-700 rounded-lg text-xs font-mono text-white focus:outline-none focus:ring-1 focus:ring-blue-500"></textarea>
            </div>
            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('addCategoryModal').classList.add('hidden')" class="px-3 py-1.5 rounded-lg bg-slate-800 text-slate-300 text-xs font-medium">Cancel</button>
                <button type="submit" class="px-4 py-1.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-medium text-xs">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection