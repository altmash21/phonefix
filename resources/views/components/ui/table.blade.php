<div class="relative w-full overflow-auto rounded-lg border border-slate-200 bg-white shadow-xs">
    <table {{ $attributes->merge(['class' => 'w-full caption-bottom text-sm border-collapse']) }}>
        {{ $slot }}
    </table>
</div>
