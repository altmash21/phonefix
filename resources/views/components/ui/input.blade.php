@props([
    'type' => 'text',
    'disabled' => false,
    'error' => null,
])

@php
    $baseClasses = 'flex h-9 w-full rounded-md border border-slate-300 bg-white px-3 py-1 text-sm font-medium text-slate-900 shadow-xs transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-slate-400 placeholder:font-normal focus-visible:outline-none focus-visible:border-indigo-600 focus-visible:ring-2 focus-visible:ring-indigo-500/20 disabled:cursor-not-allowed disabled:opacity-50 disabled:bg-slate-50';

    if ($error) {
        $baseClasses .= ' border-red-500 focus-visible:border-red-500 focus-visible:ring-red-500/20';
    }
@endphp

<input type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->merge(['class' => $baseClasses]) }}>
