@props([
    'variant' => 'default',
])

@php
    $baseClasses = 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 select-none tracking-tight';

    $variants = [
        'default'     => 'bg-indigo-600 text-white hover:bg-indigo-700',
        'primary'     => 'bg-indigo-600 text-white hover:bg-indigo-700',
        'secondary'   => 'bg-slate-100 text-slate-800 hover:bg-slate-200',
        'destructive' => 'bg-red-50 text-red-700 border border-red-200/80 hover:bg-red-100',
        'danger'      => 'bg-red-50 text-red-700 border border-red-200/80 hover:bg-red-100',
        'success'     => 'bg-emerald-50 text-emerald-700 border border-emerald-200/80 hover:bg-emerald-100',
        'warning'     => 'bg-amber-50 text-amber-800 border border-amber-200/80 hover:bg-amber-100',
        'amber'       => 'bg-amber-50 text-amber-800 border border-amber-200/80 hover:bg-amber-100',
        'blue'        => 'bg-blue-50 text-blue-700 border border-blue-200/80 hover:bg-blue-100',
        'purple'      => 'bg-purple-50 text-purple-700 border border-purple-200/80 hover:bg-purple-100',
        'outline'     => 'text-slate-800 border border-slate-300 bg-white hover:bg-slate-50',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['default']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
