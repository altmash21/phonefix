@props([
    'id',
    'title'       => null,
    'description' => null,
    'maxWidth'    => 'md',
])

@php
    $maxWidthClasses = [
        'sm'  => 'max-w-sm',
        'md'  => 'max-w-md',
        'lg'  => 'max-w-lg',
        'xl'  => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
        'full'=> 'max-w-full m-4',
    ];
    $widthClass = $maxWidthClasses[$maxWidth] ?? 'max-w-md';
@endphp

<div id="{{ $id }}"
     class="ui-dialog-backdrop fixed inset-0 z-[9999] bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4 transition-opacity duration-200"
     style="display: none; opacity: 0;"
     data-ui-dialog="{{ $id }}"
     onclick="if(event.target === this) uiDialog.close('{{ $id }}')">

    <div class="ui-dialog-content relative w-full {{ $widthClass }} bg-white rounded-xl border border-slate-200 shadow-2xl overflow-hidden transition-all duration-200 transform scale-95"
         onclick="event.stopPropagation()">

        <!-- Header -->
        @if($title || $description)
        <div class="flex items-start justify-between p-5 border-b border-slate-100 bg-white">
            <div class="space-y-1">
                @if($title)
                <h3 class="font-bold text-base text-slate-900 leading-none tracking-tight">
                    {{ $title }}
                </h3>
                @endif
                @if($description)
                <p class="text-xs text-slate-500 font-medium">
                    {{ $description }}
                </p>
                @endif
            </div>

            <button type="button"
                    onclick="uiDialog.close('{{ $id }}')"
                    class="rounded-md p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    title="Close">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        @else
        <button type="button"
                onclick="uiDialog.close('{{ $id }}')"
                class="absolute right-4 top-4 rounded-md p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500 z-10"
                title="Close">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
        @endif

        <!-- Body -->
        <div class="p-5 max-h-[80vh] overflow-y-auto">
            {{ $slot }}
        </div>

        <!-- Footer Slot -->
        @isset($footer)
        <div class="flex items-center justify-end gap-2 p-4 bg-slate-50/80 border-t border-slate-100">
            {{ $footer }}
        </div>
        @endisset
    </div>
</div>
