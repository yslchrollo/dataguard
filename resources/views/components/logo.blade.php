@props([
    'variant' => 'icon', // 'icon' | 'full' | 'badge'
    'size' => 'md',      // 'sm' | 'md' | 'lg' | 'xl'
    'showText' => true,
    'class' => '',
])

@php
    $iconSizes = [
        'sm' => 'w-6 h-6',
        'md' => 'w-8 h-8',
        'lg' => 'w-10 h-10',
        'xl' => 'w-14 h-14',
    ];

    $textSizes = [
        'sm' => 'text-xs',
        'md' => 'text-sm',
        'lg' => 'text-lg',
        'xl' => 'text-2xl',
    ];

    $iconClass = $iconSizes[$size] ?? 'w-8 h-8';
    $textClass = $textSizes[$size] ?? 'text-sm';
@endphp

@if($variant === 'full')
    <!-- Full Official Horizontal Logo -->
    <div class="inline-flex items-center justify-center {{ $class }}">
        <div class="p-2 sm:p-2.5 px-3 sm:px-4 rounded-2xl bg-white border border-slate-700/60 shadow-xl inline-flex items-center justify-center max-w-full">
            <img src="{{ asset('images/dataguard-logo.jpg') }}" 
                 alt="DataGuard - Web-Based Data Loss Prevention and Monitoring System" 
                 class="h-10 sm:h-12 w-auto object-contain select-none pointer-events-none"
                 loading="eager">
        </div>
    </div>
@elseif($variant === 'badge')
    <!-- Large Official Brand Emblem with Title -->
    <div class="flex flex-col items-center justify-center text-center {{ $class }}">
        <div class="w-16 h-16 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl shadow-teal-950/30 flex items-center justify-center p-2 mb-2">
            <img src="{{ asset('images/dataguard-icon-transparent.png') }}" 
                 alt="DataGuard Emblem" 
                 class="w-full h-full object-contain select-none">
        </div>
        @if($showText)
            <div class="text-2xl font-extrabold text-white tracking-tight">
                Data<span class="text-[#2ce2b7]">Guard</span>
            </div>
            <p class="text-[11px] font-medium tracking-wide uppercase text-slate-400 mt-0.5">
                Web-Based Data Loss Prevention and Monitoring System
            </p>
        @endif
    </div>
@else
    <!-- Compact Brand Lockup (Icon + Text) -->
    <div class="inline-flex items-center gap-2.5 {{ $class }}">
        <div class="{{ $iconClass }} shrink-0 rounded-xl overflow-hidden flex items-center justify-center bg-slate-900/60 border border-slate-800">
            <img src="{{ asset('images/dataguard-icon-transparent.png') }}" 
                 alt="DataGuard Logo" 
                 class="w-full h-full object-contain select-none">
        </div>
        @if($showText)
            <div class="flex flex-col">
                <span class="{{ $textClass }} font-bold text-white tracking-tight leading-none">
                    Data<span class="text-[#2ce2b7]">Guard</span>
                </span>
                @if($size === 'lg' || $size === 'xl')
                    <span class="text-[10px] text-slate-400 font-medium tracking-wider uppercase mt-1">
                        DLP & Monitoring System
                    </span>
                @endif
            </div>
        @endif
    </div>
@endif
