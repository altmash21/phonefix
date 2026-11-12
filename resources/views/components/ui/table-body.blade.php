<tbody {{ $attributes->merge(['class' => '[&_tr:last-child]:border-0 divide-y divide-slate-100']) }}>
    {{ $slot }}
</tbody>
