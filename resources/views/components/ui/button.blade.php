@props([
    'variant' => 'default',
    'size'    => 'default',
    'type'    => 'button',
    'as'      => 'button',
    'href'    => null,
    'disabled'=> false,
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-indigo-500 disabled:pointer-events-none disabled:opacity-50 select-none shadow-xs';

    $variants = [
        'default'     => 'bg-indigo-600 text-white shadow-sm hover:bg-indigo-700 active:bg-indigo-800',
        'primary'     => 'bg-indigo-600 text-white shadow-sm hover:bg-indigo-700 active:bg-indigo-800',
        'secondary'   => 'bg-slate-100 text-slate-900 shadow-xs hover:bg-slate-200 active:bg-slate-300',
        'outline'     => 'border border-slate-200 bg-white shadow-xs hover:bg-slate-50 hover:text-slate-900 text-slate-700 active:bg-slate-100',
        'destructive' => 'bg-red-600 text-white shadow-xs hover:bg-red-700 active:bg-red-800',
        'danger'      => 'bg-red-600 text-white shadow-xs hover:bg-red-700 active:bg-red-800',
        'success'     => 'bg-emerald-600 text-white shadow-xs hover:bg-emerald-700 active:bg-emerald-800',
        'ghost'       => 'hover:bg-slate-100 hover:text-slate-900 text-slate-700 shadow-none',
        'link'        => 'text-indigo-600 underline-offset-4 hover:underline shadow-none p-0 h-auto',
    ];

    $sizes = [
        'default' => 'h-9 px-4 py-2 text-sm',
        'sm'      => 'h-8 px-3 text-xs rounded-md',
        'xs'      => 'h-7 px-2.5 text-xs rounded-md',
        'lg'      => 'h-10 px-8 text-base rounded-md',
        'icon'    => 'h-9 w-9 p-0 flex items-center justify-center',
        'icon-sm' => 'h-7 w-7 p-0 flex items-center justify-center',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['default']) . ' ' . ($sizes[$size] ?? $sizes['default']);
@endphp

@if($as === 'a' || $href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
