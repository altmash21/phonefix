<tr {{ $attributes->merge(['class' => 'border-b border-slate-100 transition-colors hover:bg-slate-50/80 data-[state=selected]:bg-slate-50']) }}>
    {{ $slot }}
</tr>
