@props([
    'variant'     => 'default',
    'title'       => null,
    'description' => null,
    'icon'        => null,
])

@php
    $baseClasses = 'relative w-full rounded-lg border p-4 text-sm [&>svg~*]:pl-7 [&>svg+div]:translate-y-[-3px] [&>svg]:absolute [&>svg]:left-4 [&>svg]:top-4 [&>svg]:text-slate-950 flex gap-3 items-start';

    $variants = [
        'default'     => 'bg-white text-slate-950 border-slate-200',
        'destructive' => 'border-red-200/80 bg-red-50 text-red-900 [&>svg]:text-red-600',
        'danger'      => 'border-red-200/80 bg-red-50 text-red-900 [&>svg]:text-red-600',
        'success'     => 'border-emerald-200/80 bg-emerald-50 text-emerald-900 [&>svg]:text-emerald-600',
        'warning'     => 'border-amber-200/80 bg-amber-50 text-amber-900 [&>svg]:text-amber-600',
        'info'        => 'border-blue-200/80 bg-blue-50 text-blue-900 [&>svg]:text-blue-600',
    ];

    $defaultIcons = [
        'default'     => 'info',
        'destructive' => 'alert-triangle',
        'danger'      => 'alert-triangle',
        'success'     => 'check-circle-2',
        'warning'     => 'alert-circle',
        'info'        => 'info',
    ];

    $resolvedIcon = $icon ?: ($defaultIcons[$variant] ?? 'info');
    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['default']);
@endphp

<div role="alert" {{ $attributes->merge(['class' => $classes]) }}>
    <div class="shrink-0 mt-0.5">
        <i data-lucide="{{ $resolvedIcon }}" class="w-4 h-4"></i>
    </div>
    <div class="space-y-1">
        @if($title)
        <h5 class="font-bold leading-none tracking-tight">
            {{ $title }}
        </h5>
        @endif
        @if($description)
        <div class="text-xs [&_p]:leading-relaxed opacity-90 font-medium">
            {{ $description }}
        </div>
        @endif
        {{ $slot }}
    </div>
</div>
