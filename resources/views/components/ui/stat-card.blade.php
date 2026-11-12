@props([
    'title',
    'value',
    'icon'       => null,
    'iconColor'  => 'text-indigo-600',
    'sub'        => null,
    'change'     => null,
    'changeType' => 'positive', // positive, negative, neutral
])

<div {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200/90 bg-white p-5 shadow-xs transition-all hover:border-slate-300 hover:shadow-sm flex flex-col justify-between']) }}>
    <div class="flex items-center justify-between space-y-0 pb-2">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">
            {{ $title }}
        </span>
        @if($icon)
        <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center {{ $iconColor }}">
            <i data-lucide="{{ $icon }}" class="w-4 h-4"></i>
        </div>
        @endif
    </div>

    <div class="my-1.5">
        <div class="text-2xl font-black tracking-tight text-slate-900 font-mono">
            {{ $value }}
        </div>
    </div>

    @if($sub || $change)
    <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
        @if($change)
            @if($changeType === 'positive')
                <span class="text-emerald-600 font-bold flex items-center gap-0.5">
                    <i data-lucide="trending-up" class="w-3 h-3"></i> {{ $change }}
                </span>
            @elseif($changeType === 'negative')
                <span class="text-red-600 font-bold flex items-center gap-0.5">
                    <i data-lucide="trending-down" class="w-3 h-3"></i> {{ $change }}
                </span>
            @else
                <span class="text-slate-600 font-bold">{{ $change }}</span>
            @endif
        @endif
        @if($sub)
            <span class="text-slate-400">&bull;</span>
            <span>{{ $sub }}</span>
        @endif
    </div>
    @endif
</div>
